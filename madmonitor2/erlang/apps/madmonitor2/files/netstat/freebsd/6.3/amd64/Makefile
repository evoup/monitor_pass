#	@(#)Makefile	8.1 (Berkeley) 6/12/93
# $FreeBSD: src/usr.bin/netstat/Makefile,v 1.26.2.4 2007/10/03 19:56:17 obrien Exp $

PROG=	netstat
SRCS=	bpf.c if.c inet.c inet6.c main.c mbuf.c mcast.c mroute.c ipx.c route.c \
	unix.c atalk.c netgraph.c mroute6.c ipsec.c pfkey.c

WARNS?=	3

CFLAGS+=-DIPSEC
.if !defined(NO_INET6)
CFLAGS+=-DINET6
CFLAGS+=-DFAST_IPSEC
.endif

BINGRP=	kmem
BINMODE=2555
DPADD=	${LIBKVM} ${LIBIPX} ${LIBMEMSTAT} ${LIBNETGRAPH} ${LIBUTIL}
LDADD=	-lkvm -lipx -lmemstat -lnetgraph -lutil

.include <bsd.prog.mk>
