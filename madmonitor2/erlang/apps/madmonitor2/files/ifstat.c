#include "erl_nif.h"
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/sysctl.h>
#include <net/if.h>
#include <net/if_mib.h>

#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <err.h>
#include <errno.h>
#include "time.h"
#include <unistd.h>

char * trim(char * desc,char * src,char * seps);
int cmdifstat(const char *, const char *);
int tryTime=0;
#define MAX_BUFFER 3000
char globalBuf[MAX_BUFFER];
time_t t;
struct tm *lt;

SLIST_HEAD(, if_stat)       curlist; /*创建单向链表curlist*/ 
SLIST_HEAD(, if_stat_disp)  displist; /*创建单向链表displist*/ 
struct if_stat {
    SLIST_ENTRY(if_stat)     link;
    char    if_name[IF_NAMESIZE]; /* 在if.h中定义 */ 
    struct  ifmibdata if_mib;
    struct  timeval tv;
    struct  timeval tv_lastchanged;
    u_long  if_in_curtraffic;
    u_long  if_out_curtraffic;
    u_long  if_in_traffic_peak;
    u_long  if_out_traffic_peak;
    u_int   if_row;         /* Index into ifmib sysctl */
    u_int   if_ypos;        /* 0 if not being displayed */
    u_int   display;
};
#define IFSTAT_ERR(n, s)    do {                    \
    putchar('^L');                          \
    closeifstat(wnd);                       \
    err((n), (s));                          \
} while (0)

#define TOPLINE 3
#define ROW_SPACING (3)
#define STARTING_ROW      (TOPLINE + 1)


/*
 * We want to right justify our interface names against the first column
 * (first sixteen or so characters), so we need to do some alignment.
 */
 void
right_align_string(struct if_stat *ifp)
{
    int  str_len = 0, pad_len = 0;
    char    *newstr = NULL, *ptr = NULL;

    if (ifp == NULL || ifp->if_mib.ifmd_name == NULL)
        return;
    else {
        /* string length + '\0' */
        str_len = strlen(ifp->if_mib.ifmd_name)+1;
        pad_len = IF_NAMESIZE-(str_len);

        newstr = ifp->if_name;
        ptr = newstr + pad_len;
        (void)memset((void *)newstr, (int)' ', IF_NAMESIZE);
        (void)strncpy(ptr, (const char *)&ifp->if_mib.ifmd_name,
                  str_len);
    }

    return;
}

/*
 * This function iterates through our list of interfaces, identifying
 * those that are to be displayed (ifp->display = 1).  For each interf-
 * rface that we're displaying, we generate an appropriate position for
 * it on the screen (ifp->if_ypos).
 *
 * This function is called any time a change is made to an interface's
 * ``display'' state.
 */
void
sort_interface_list(void)
{
    struct  if_stat *ifp = NULL;
    u_int   y = 0;

    y = STARTING_ROW;
    SLIST_FOREACH(ifp, &curlist, link) {
        if (ifp->display) {
            ifp->if_ypos = y;
            y += ROW_SPACING;
        }
    }
}

 void
getifmibdata(int row, struct ifmibdata *data)
{
    size_t  datalen = 0;
      int name[] = { CTL_NET,
                       PF_LINK,
                       NETLINK_GENERIC,
                       IFMIB_IFDATA,
                       0,
                       IFDATA_GENERAL };
    datalen = sizeof(*data);
    name[4] = row;

    if ((sysctl(name, 6, (void *)data, (size_t *)&datalen, (void *)NULL,
                        (size_t)0) != 0) && (errno != ENOENT)) {
        printf("sysctl error getting interface data\n");
    }
        /*IFSTAT_ERR(2, "sysctl error getting interface data");*/
}



unsigned int
getifnum(void)
{
    u_int   data    = 0;
    size_t  datalen = 0;
      int name[] = { CTL_NET,
                       PF_LINK,
                       NETLINK_GENERIC,
                       IFMIB_SYSTEM,
                       IFMIB_IFCOUNT };

    datalen = sizeof(data);
    if (sysctl(name, 5, (void *)&data, (size_t *)&datalen, (void *)NULL,
                    (size_t)0) != 0) {
        printf("sysctl error\n");
    }
        /*IFSTAT_ERR(1, "sysctl error");*/
    return data;
}

int
initifstat(void)
{
    struct   if_stat *p = NULL;
    u_int    n = 0, i = 0;

    n = getifnum();
    printf("[getifnum:%d]\n",n);
    if (n <= 0)
        return -1;

    SLIST_INIT(&curlist);

    for (i = 0; i < n; i++) {
        p = (struct if_stat *)calloc(1, sizeof(struct if_stat));
        if (p == NULL) {
            printf("out of memory\n");
        }
            /*IFSTAT_ERR(1, "out of memory");*/
        SLIST_INSERT_HEAD(&curlist, p, link);
        p->if_row = i+1;
        getifmibdata(p->if_row, &p->if_mib);
        right_align_string(p);

        /*
         * Initially, we only display interfaces that have
         * received some traffic.
         */
        if (p->if_mib.ifmd_data.ifi_ibytes != 0)
            p->display = 1;
    }

    sort_interface_list();

    return 1;
}

void
fetchifstat(void)
{
    struct  if_stat *ifp = NULL;
    struct  timeval tv, new_tv, old_tv;
    double  elapsed = 0.0;
    u_int   new_inb, new_outb, old_inb, old_outb = 0;
    u_int   we_need_to_sort_interface_list = 0;

    SLIST_FOREACH(ifp, &curlist, link) {
        /*
         * Grab a copy of the old input/output values before we
         * call getifmibdata().
         */
        old_inb = ifp->if_mib.ifmd_data.ifi_ibytes;
        old_outb = ifp->if_mib.ifmd_data.ifi_obytes;
        ifp->tv_lastchanged = ifp->if_mib.ifmd_data.ifi_lastchange;

        if (gettimeofday(&new_tv, (struct timezone *)0) != 0) {
            printf("error getting time of day");
        }
            /*IFSTAT_ERR(2, "error getting time of day");*/
        (void)getifmibdata(ifp->if_row, &ifp->if_mib);


                new_inb = ifp->if_mib.ifmd_data.ifi_ibytes;
                new_outb = ifp->if_mib.ifmd_data.ifi_obytes;

        /* Display interface if it's received some traffic. */
        if (new_inb > 0 && old_inb == 0) {
            ifp->display = 1;
            we_need_to_sort_interface_list++;
        }

        /*
         * The rest is pretty trivial.  Calculate the new values
         * for our current traffic rates, and while we're there,
         * see if we have new peak rates.
         */
                old_tv = ifp->tv;
                timersub(&new_tv, &old_tv, &tv);
                elapsed = tv.tv_sec + (tv.tv_usec * 1e-6);

        ifp->if_in_curtraffic = new_inb - old_inb;
        ifp->if_out_curtraffic = new_outb - old_outb;

        /*
         * Rather than divide by the time specified on the comm-
         * and line, we divide by ``elapsed'' as this is likely
         * to be more accurate.
         */
                ifp->if_in_curtraffic /= elapsed;
                ifp->if_out_curtraffic /= elapsed;

        if (ifp->if_in_curtraffic > ifp->if_in_traffic_peak)
            ifp->if_in_traffic_peak = ifp->if_in_curtraffic;

        if (ifp->if_out_curtraffic > ifp->if_out_traffic_peak)
            ifp->if_out_traffic_peak = ifp->if_out_curtraffic;

        ifp->tv.tv_sec = new_tv.tv_sec;
        ifp->tv.tv_usec = new_tv.tv_usec;

    }

    if (we_need_to_sort_interface_list)
        sort_interface_list();

    return;
}

void showifstat(void)
{
    struct  if_stat *ifp = NULL;
    SLIST_FOREACH(ifp, &curlist, link) {
        if (ifp->display == 0)
            continue;
        /*PUTNAME(ifp);*/
        printf("%s\n",ifp->if_name);
        printf("if_in_curtraffic%ld\n",ifp->if_in_curtraffic);
        printf("if_out_curtraffic%ld\n",ifp->if_out_curtraffic);
        printf("if_in_traffic_peak%ld\n",ifp->if_in_traffic_peak);
        printf("if_out_traffic_peak%ld\n",ifp->if_out_traffic_peak);
        /*PUTRATE(col2, ifp->if_ypos);*/
        /*PUTRATE(col3, ifp->if_ypos);*/
        /*PUTTOTAL(col4, ifp->if_ypos);*/
        printf("if_in_traffic_total%ld\n",ifp->if_mib.ifmd_data.ifi_ibytes);
        printf("if_out_traffic_total%ld\n",ifp->if_mib.ifmd_data.ifi_obytes);
        char tmpStr[200];
        if (tryTime>0) {
            t = time(NULL);
            printf("test%d\n",tryTime);
            strcat(globalBuf,"#");
            char szResult[1024]="";
            memset(szResult,0,1024);
            trim(szResult,ifp->if_name," ");
            sprintf(tmpStr,"%s",szResult);
            strcat(globalBuf,tmpStr);
            /*strcat(globalBuf,"|");*/
            /*sprintf(tmpStr,"%ld",ifp->if_in_curtraffic);*/
            /*strcat(globalBuf,tmpStr);*/
            /*strcat(globalBuf,"|");*/
            /*sprintf(tmpStr,"%ld",ifp->if_out_curtraffic);*/
            /*strcat(globalBuf,tmpStr);*/
            strcat(globalBuf,"|");
            sprintf(tmpStr,"%ld",ifp->if_mib.ifmd_data.ifi_ibytes);
            strcat(globalBuf,tmpStr);
            strcat(globalBuf,"|");
            sprintf(tmpStr,"%ld",ifp->if_mib.ifmd_data.ifi_obytes);
            strcat(globalBuf,tmpStr);
            strcat(globalBuf,"|");
            sprintf(tmpStr,"%ld",time(&t));
            strcat(globalBuf,tmpStr);
        }
    }

    return;
}

int
cmdifstat(const char *cmd, const char *args)
{
    /*int retval = 0;*/
    int retval = 1;

    /*retval = ifcmd(cmd, args);*/
    /* ifcmd() returns 1 on success */
    if (retval == 1) {
        showifstat();
        /*refresh();*/
    }

    return retval;
}

static ERL_NIF_TERM ifstat_ex(ErlNifEnv* env, int argc, const ERL_NIF_TERM argv[])
{
    initifstat();
    memset(globalBuf,'\0',MAX_BUFFER);
    do {
        fetchifstat();
        cmdifstat("", "-ifstat 1\0");
        sleep(1);
        tryTime++;
    } while (tryTime<2);
    tryTime=0;
    printf("[%s]",globalBuf);
    return enif_make_string(env, globalBuf, ERL_NIF_LATIN1);
}

char * trim(char * desc,char * src,char * seps)
{
    char * token=NULL;
    /* Establish string and get the first token: */
    token = strtok(src, seps);
    while( token != NULL )
    {
        /* While there are tokens in "string" */
        printf( " %s\n", token );
        strcat(desc,token);
        /* Get next token: */
        token = strtok( NULL, seps );
    }
    return desc;
}

static ErlNifFunc nif_funcs[] =
{
   {"ifstat_ex", 0, ifstat_ex}
};

ERL_NIF_INIT(ifstat,nif_funcs,NULL,NULL,NULL,NULL)
