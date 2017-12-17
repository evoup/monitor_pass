#include "erl_nif.h"
#include <stdlib.h>
#include <stdio.h>

static ERL_NIF_TERM getloadavg_ex(ErlNifEnv* env, int argc, const ERL_NIF_TERM argv[])
{
   double load[3];
   char buf[30];
   if (getloadavg(load,3)==-1) {
       sprintf(buf, "%f", 0.00);
   } else {
       sprintf(buf, "%f", load[0]);
   }
   return enif_make_string(env, buf, ERL_NIF_LATIN1);
}

static ErlNifFunc nif_funcs[] =
{
   {"getloadavg_ex", 0, getloadavg_ex}
};

ERL_NIF_INIT(getload,nif_funcs,NULL,NULL,NULL,NULL)
