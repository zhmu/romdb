#!/usr/bin/env python3

import sys, os
sys.path.append(os.path.join(os.path.dirname(__file__), 'lib'))
import romdb

if len(sys.argv) != 3:
	print("usage: %s db.xml file.db" % sys.argv[0])
	sys.exit(0)
dbxmlfname = sys.argv[1]
filedbfname = sys.argv[2]

fields = romdb.load_field_xml(dbxmlfname)
records = romdb.decode_db(filedbfname, fields)
num = 0
for k, v in records.items():
	print('  <entry n="%s" guid="%s">' % (num, k))
	for fld in fields:
		print('    <%s value="%s"/>' % (fld['name'], v[fld['name']]))
	print('  </entry>')
	num = num + 1

# vim:set ts=2 sw=2:
