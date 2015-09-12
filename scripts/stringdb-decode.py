#!/usr/bin/python3

import sys, os
sys.path.append(os.path.join(os.path.dirname(__file__), 'lib'))
import romdb

if len(sys.argv) != 2:
	print("usage: %s string_....db" % sys.argv[0])
	sys.exit(0)

fname = sys.argv[1]
strings = romdb.read_stringdb(fname)
for k, v in strings.items():
	print("\'%s\': %s" % (k, v))

