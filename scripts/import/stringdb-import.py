#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

def import_strings(cur, strings, idtype, table, prefix, suffix):
	cur.execute('DROP TABLE IF EXISTS ' + table + ' CASCADE')
	cur.execute('CREATE TABLE ' + table + ' (id ' + idtype + ' PRIMARY KEY, content CITEXT NOT NULL);')
	if suffix:
		d = { k[len(prefix):-len(suffix)]:v for k, v in strings.items() if k.startswith(prefix) and k.endswith(suffix) }
	else:
		d = { k[len(prefix):]:v for k, v in strings.items() if k.startswith(prefix) }
	for k, v in d.items():
		cur.execute('INSERT INTO ' + table + ' (id, content) VALUES (%s,%s)', (k, v))

fname = Configuration.data_dir() + "/string_eneu.db"

conn = DBConnection()
cur = conn.cursor()

strings = romdb.read_stringdb(fname)

import_strings(cur, strings, 'INTEGER', 'sys_name', 'Sys', '_name')
import_strings(cur, strings, 'INTEGER', 'sys_shortnote', 'Sys', '_shortnote')
import_strings(cur, strings, 'INTEGER', 'sys_weareqtype', 'SYS_WEAREQTYPE_', '')
import_strings(cur, strings, 'INTEGER', 'sys_weapontype', 'SYS_WEAPON_TYPE', '')
import_strings(cur, strings, 'INTEGER', 'sys_weaponpos', 'SYS_WEAPON_POS', '')
import_strings(cur, strings, 'INTEGER', 'sys_armorpos', 'SYS_ARMORPOS_', '')
import_strings(cur, strings, 'INTEGER', 'sys_armortype', 'SYS_ARMORTYPE_', '')
import_strings(cur, strings, 'VARCHAR(128)', 'sys_sc', 'SC_', '')
import_strings(cur, strings, 'VARCHAR(128)', 'sys_zone', 'ZONE_', '')

conn.commit()
conn.close()
