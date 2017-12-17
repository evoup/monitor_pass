all: compile

depends:
	chmod +x ./rebar ./make.sh
	./rebar get-deps
	./rebar update-deps

compile:
	chmod +x ./rebar ./make.sh
	sed  's/{vsn, "[0-9].[0-9].[0-9]"}/{vsn,""}/g' ./deps/log4erl/src/log4erl.app.src > ./deps/log4erl/src/log4erl.app.src.tmp
	mv ./deps/log4erl/src/log4erl.app.src.tmp ./deps/log4erl/src/log4erl.app.src
	./rebar compile
	./make.sh

clean:
	chmod +x ./rebar ./make.sh
	./rebar clean

release:
	chmod +x ./rebar ./make.sh
	./rebar generate
	mkdir ./rel/madmonitor2/priv
	cp -r ./apps/madmonitor2/priv/* ./rel/madmonitor2/priv/
	cp -r ./apps/madmonitor2/files/sdc ./rel/madmonitor2/sdc
	chmod +x ./rel/madmonitor2/bin/madmonitor2
	cp ./rel/madmonitor2/bin/madmonitor2 ./rel/madmonitor2/bin/madmonitor2.attach 
	patch ./rel/madmonitor2/bin/madmonitor2 < ./apps/madmonitor2/files/madmonitor2.patch

remake:
	make clean && make && make release

.PHONY: all depends compile

