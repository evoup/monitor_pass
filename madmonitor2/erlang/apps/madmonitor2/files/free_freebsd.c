/*
 * free.c - Display FreeBSD memory information
 * Wed Nov 26 19:34:54 IST 2008 vinod <vinod@segfault.in>
 * License: http://opensource.org/licenses/BSD-2-Clause
 */

#include "erl_nif.h"
#include <sys/types.h>
#include <sys/sysctl.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>

const char *version = "$Id: free,v 0.1.3 2008/11/26 19:34:54 IST vinod $";
#define MAX_BUFFER 3000 
char * getFree(int,char **);
char globalBuf[MAX_BUFFER];

int
get_sysctl(char *name)
{
	int mib[4], value, i;
	size_t len, miblen = 1;

	for(i = 0; name[i] != '\0'; i++) 
		if(name[i] == '.')
			miblen++;
	len = miblen;
	sysctlnametomib(name, mib, &len);
	len = sizeof(value);
	sysctl(mib, miblen, &value, &len, NULL, 0);

	return value;
}

void
usage(void)
{
	fprintf(stderr, "usage: free [-b|-k|-m|-g] [-t] [-v]\n" \
	"  -b,-k,-m,-g show output in bytes, KB, MB, or GB\n" \
	"  -t display logical summary for RAM\n" \
	"  -v display version information and exit\n");
}


static ERL_NIF_TERM free_freebsd_ex(ErlNifEnv* env, int argc, const ERL_NIF_TERM argv[])
{
    optind=1;
    char *emptyCmdStr[2]={"\0"};
    char outBuf[300];
    memset(globalBuf,'\0',MAX_BUFFER);
    sprintf(outBuf,"%s",getFree(1,emptyCmdStr));
    return enif_make_string(env, outBuf, ERL_NIF_LATIN1);
}

char *
/*main(int argc, char *argv[])*/
getFree(int argc, char *argv[])
{
    char fileName[MAX_BUFFER];
    memset(fileName,'\0',MAX_BUFFER);
	int c, vflag = 0, tflag = 0;
	int factor = 1;
	long int physmem, realmem;
	long int vmactive, vminactive, vmfree, vmcache, vmpage, vmwire;
	long int memfree, memused;
	long int pagesize;

	opterr = 0;

	while ((c = getopt(argc, argv, "bghkmtv")) != -1) {
		switch (c) {
			case 'b':
				factor = 1;
				break;
			case 'g':
				factor = 1024*1024*1024;

			case 'h':
				usage();
				exit(EXIT_SUCCESS);
			case 'k':
				factor = 1024;
				break;
			case 'm':
				factor = 1024*1024;
				break;
			case 't':
				tflag = 1;
				break;
			case 'v':
				vflag = 1;
				break;
			case '?':
			default:
				fprintf(stderr, "%s: invalid option -- %c\n", argv[0], optopt);
				usage();
				exit(EXIT_FAILURE);
		}
	}

	argc -= optind;
	argv += optind;

	if(vflag) {
		fprintf(stderr, "%s\nbuilt %s %s\n", version,
				__DATE__, __TIME__);
		exit(EXIT_SUCCESS);
	}

	physmem    = labs(get_sysctl("hw.physmem"));
	realmem    = labs(get_sysctl("hw.realmem"));
	pagesize   = labs(get_sysctl("hw.pagesize"));

	vmpage     = labs(get_sysctl("vm.stats.vm.v_page_count") * pagesize);
	vmwire     = labs(get_sysctl("vm.stats.vm.v_wire_count") * pagesize);
	vmactive   = labs(get_sysctl("vm.stats.vm.v_active_count") * pagesize);
	vminactive = labs(get_sysctl("vm.stats.vm.v_inactive_count") * pagesize);
	vmcache    = labs(get_sysctl("vm.stats.vm.v_cache_count") * pagesize);
	vmfree     = labs(get_sysctl("vm.stats.vm.v_free_count") * pagesize);

	printf("         %15s %15s %15s %15s %15s %15s\n", "total", "active", "free", "inactive", "wire", "cached");
	printf("Memory:  %15ld %15ld %15ld %15ld %15ld %15ld\n",
			realmem/factor,
			vmactive/factor,
			vmfree/factor,
			vminactive/factor,
			vmwire/factor,
			vmcache/factor);
    /*{{{construct return value*/
    char tempBuf[100];
    memset(tempBuf,'\0',100);
    sprintf(tempBuf,"%ld",realmem/factor);
    strcat(fileName,tempBuf);
    strcat(fileName,"|");
    memset(tempBuf,'\0',100);
    sprintf(tempBuf,"%ld",vmactive/factor);
    strcat(fileName,tempBuf);
    strcat(fileName,"|");
    memset(tempBuf,'\0',100);
    sprintf(tempBuf,"%ld",vmfree/factor);
    strcat(fileName,tempBuf);
    strcat(fileName,"|");
    memset(tempBuf,'\0',100);
    sprintf(tempBuf,"%ld",vminactive/factor);
    strcat(fileName,tempBuf);
    strcat(fileName,"|");
    memset(tempBuf,'\0',100);
    sprintf(tempBuf,"%ld",vmwire/factor);
    strcat(fileName,tempBuf);
    strcat(fileName,"|");
    memset(tempBuf,'\0',100);
    sprintf(tempBuf,"%ld",vmcache/factor);
    strcat(fileName,tempBuf);
    printf("[%s]\n",fileName);
    /*}}}*/
	/*
	 * logical summary
	 */
	if(tflag) {
		memfree = vminactive + vmfree + vmcache;
		memused	= realmem - memfree;

		printf("Summary: %15ld %15ld %15ld\n",
				realmem/factor,
				memused/factor,
				memfree/factor);
	}

	/*return (EXIT_SUCCESS);*/
    return strcat(globalBuf,fileName);
}

static ErlNifFunc nif_funcs[] =
{
   {"free_freebsd_ex", 0, free_freebsd_ex}
};

ERL_NIF_INIT(free_freebsd,nif_funcs,NULL,NULL,NULL,NULL)
