OBJS=		fdbtool.o fdb.o png.o crc32.o
LIBS=		-lz
CPPFLAGS+=	-D_XOPEN_SOURCE=500 -g -Wall -Werror

fdbtool:	$(OBJS)
		$(CXX) -o fdbtool $(OBJS) $(LIBS)

clean:
		rm -f fdbtool $(OBJS)
