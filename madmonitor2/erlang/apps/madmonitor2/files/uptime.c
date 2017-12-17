#include "erl_nif.h"
#include <sys/time.h>
#include <stdio.h>
#include <stdlib.h>
static ERL_NIF_TERM uptime_ex(ErlNifEnv* env, int argc, const ERL_NIF_TERM argv[])
{
    struct timespec tp;
    time_t uptime;
    int days, hrs, mins, secs;
    char buf[256];
    /*
     * Print how long system has been up.
     */
    if (clock_gettime(CLOCK_MONOTONIC, &tp) != -1) {
        uptime = tp.tv_sec;
        printf("[%ld]",(long)uptime);
        sprintf(buf, "%ld", (long)uptime);
        if (uptime > 60)
            uptime += 30;
        days = uptime / 86400;
        uptime %= 86400;
        hrs = uptime / 3600;
        uptime %= 3600;
        mins = uptime / 60;
        secs = uptime % 60;
        (void)printf(" up");
        if (days > 0)
            (void)printf(" %d day%s,", days, days > 1 ? "s" : "");
        if (hrs > 0 && mins > 0)
            (void)printf(" %2d:%02d,", hrs, mins);
        else if (hrs > 0)
            (void)printf(" %d hr%s,", hrs, hrs > 1 ? "s" : "");
        else if (mins > 0)
            (void)printf(" %d min%s,", mins, mins > 1 ? "s" : "");
        else
            (void)printf(" %d sec%s,", secs, secs > 1 ? "s" : "");
    }
    return enif_make_string(env, buf, ERL_NIF_LATIN1);
}

static ErlNifFunc nif_funcs[] =
{
   {"uptime_ex", 0, uptime_ex}
};

ERL_NIF_INIT(uptime,nif_funcs,NULL,NULL,NULL,NULL)
