#include "erl_nif.h"
#include <sys/param.h>
#include <stdlib.h>
#include <stdio.h>
#include <sys/ucred.h>
#include <sys/mount.h>
#include <string.h>
#define MAX_DISKBUFFER 3000 
char * getDiskInfo(char *);

static ERL_NIF_TERM getdisk_freebsd_ex(ErlNifEnv* env, int argc, const ERL_NIF_TERM argv[])
{
    char fileName[MAX_DISKBUFFER];
    return enif_make_string(env, getDiskInfo(fileName), ERL_NIF_LATIN1);
}

static ErlNifFunc nif_funcs[] =
{
   {"getdisk_freebsd_ex", 0, getdisk_freebsd_ex}
};

ERL_NIF_INIT(getdisk_freebsd,nif_funcs,NULL,NULL,NULL,NULL)

char * getDiskInfo(char * word) {
    struct statfs *mntbuf,diskInfo;
    size_t mntsize;
    mntsize = getmntinfo(&mntbuf, MNT_NOWAIT);
    unsigned long long blocksize,totalsize,availsize,freeDisk,used;
    int i;
    memset(word,'\0',MAX_DISKBUFFER);
    for (i = 0; i < mntsize; i++) {
        statfs(mntbuf[i].f_mntonname,&diskInfo);
        blocksize = diskInfo.f_bsize;// 每个block里面包含的字节数
        totalsize = blocksize * diskInfo.f_blocks;//总的字节数
        availsize  = diskInfo.f_bavail*blocksize;
        freeDisk = diskInfo.f_bfree*blocksize; //再计算下剩余的空间大小
        used = totalsize - freeDisk;
        /* 1024 换算成KB单位*/
        /*组成*MOUNTPOINT|TOTALSIZE|DISKFREE|USED|AVAIL|TOTALINODE|IUSED|IFREE的字符串*/
        strcat(word,"*");
        strcat(word,mntbuf[i].f_mntonname);
        strcat(word,"|");
        char tempBuf[100];
        sprintf(tempBuf,"%llu",totalsize>>10);
        strcat(word,tempBuf);
        strcat(word,"|");
        sprintf(tempBuf,"%llu",freeDisk>>10);
        strcat(word,tempBuf);
        strcat(word,"|");
        sprintf(tempBuf,"%llu",used>>10);
        strcat(word,tempBuf);
        strcat(word,"|");
        sprintf(tempBuf,"%llu",availsize>>10);
        strcat(word,tempBuf);
        strcat(word,"|");
        sprintf(tempBuf,"%ld",diskInfo.f_files);
        strcat(word,tempBuf);
        strcat(word,"|");
        sprintf(tempBuf,"%ld",diskInfo.f_files-diskInfo.f_ffree);
        strcat(word,tempBuf);
        strcat(word,"|");
        sprintf(tempBuf,"%ld",diskInfo.f_ffree);
        strcat(word,tempBuf);
    }
    return word;
}
