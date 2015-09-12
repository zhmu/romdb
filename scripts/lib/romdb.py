#!/usr/bin/env python3

import struct
import sys
from io import BytesIO
from lxml import etree
from dbconnection import DBConnection

# converts \0-terminated buffer 'b' to a string
def get_z_string(b):
	s = b.decode('utf-8')
	return s.split("\x00")[0]

# loads field definitions from fname; returns list of definitions
def load_field_xml(fname):
	fields = []
	with open(fname, "r") as f:
		tree = etree.parse(f)
		root = tree.getroot()
		cur_offset = 0
		gap_num = 1
		for child in root:
			if child.tag != "field":
				continue
			name = child.get('name')
			type = child.get('type')
			ref = child.get('ref')
			size = int(child.get('size'))
			offset = int(child.get('offset'), 16)
			if offset != cur_offset:
				gap_len = (offset - cur_offset)
				fields.append({ 'name': 'gap' + str(gap_num), 'type': 'u8', 'size': gap_len})
				gap_num = gap_num + 1
			fields.append({ 'name': name, 'type': type, 'size': size, 'ref': ref })
			cur_offset = offset + size
	return fields

# opens a rom DB file with field defintions and returns a map with field data
def decode_db(fname, fields):
	with open(fname, "rb") as f:
		# 0..7f = description
		desc = f.read(128)
		# 80..83 = id
		id = struct.unpack('i', f.read(4))[0]
		if id != 0x6396:
			print('invalid magic value, not a db file?')
			return
		entry_count = struct.unpack('i', f.read(4))[0]
		entry_size = struct.unpack('i', f.read(4))[0]

		print("entries: %d size: %d" % (entry_count, entry_size))

		# verify entry length
		len = 0
		for fld in fields:
			#print('@%x: %s' % (len, fld['name']))
			if fld['size']:
				len += int(fld['size'])
			else:
				len += 4 # XXX
		if len != entry_size:
			# XXX We should just add a dummy gap at the end
			print("xml fields/file fields mismatch (xml describes %d, file claims %d)" % (len, entry_size))
			return

		records = { }
		for i in range(entry_count): # XXX skips the final one, but that is always corrupt (?)
			f.seek(128 + 4 + 4 + 4 + i * entry_size)
			#print('  <entry id="%d">' % i)
			o = { }
			id = 0
			for fld in fields:
				val = 0
				if fld['type'] == "u32" and fld['size'] == 4:
					val = struct.unpack('i', f.read(4))[0]
				elif fld['type'] == "bool4" and fld['size'] == 4:
					val = struct.unpack('i', f.read(4))[0]
				elif fld['type'] == "float" and fld['size'] == 4:
					val = struct.unpack('f', f.read(4))[0]
				elif fld['type'] == "u8":
					val = struct.unpack(str(fld['size']) + 's', f.read(fld['size']))[0]
				elif fld['type'] == "string":
					val = get_z_string(struct.unpack(str(fld['size']) + 's', f.read(fld['size']))[0])
				else:
					print('unknown type "%s"' % fld['type'])
					break
				o[fld['name']] = val
				if fld['name'] == 'guid':
					id = val
			if id == 0:
				id = o[fields[0]['name']] # just take first field
			records[id] = o
			#print('  </entry>')
		return records

# reads a rom string db file and returns a dict of key -> value
def read_stringdb(fname):
	strings = {}
	with open(fname, "rb") as f:
		try:
			while True:
				name = get_z_string(f.read(44))
				unk = f.read(5 * 4)
				length = struct.unpack('i', f.read(4))[0]
				content = get_z_string(f.read(length))
				strings[name] = content
		except UnicodeDecodeError:
			pass
	return strings

class DBFields:
	class InvalidType(Exception):
		pass

	# convert xml field definitions to database field definitions
	# 'cursor' is the database cursor to use
	# 'tablename' is the name of the table to use
	# 'skip' is the list of field name prefixes to ignore (gap is always ignored)
	# 'guid' is always the primary key
	def __init__(self, cursor, tablename, fields, skip):
		self._cursor = cursor 
		self._table = tablename
		self._fields = []
		skip.append('gap') # always skip gaps
		for fld in fields:
			if fld['type'] == "u32" or fld['type'] == "bool4":
				db_type = "INTEGER"
			elif fld['type'] == "float":
				db_type = "FLOAT"
			elif fld['type'] == "u8" or fld['type'] == "string":
				db_type = "VARCHAR"
			else:
				raise InvalidType("Cannot map type '%s'" % fld['type'])
			# skip the field if the name starts with anything in the skip list
			if [ s for s in skip if fld['name'].startswith(s) ]:
				continue

			db_extra = ""
			if fld['name'] == "guid":
				db_extra = "PRIMARY KEY "
			db_extra += "NOT NULL"

			self._fields.append([fld['name'], db_type, db_extra])

		# create the insertion query
		self._ins_query = "INSERT INTO " + self._table + " ("
		self._ins_query += ','.join([ k[0] for k in self._fields ])
		self._ins_query += ") VALUES ("
		self._ins_query += ','.join([ '%s' for k in self._fields ])
		self._ins_query += ")"

	# recreates a table 'CREATE TABLE' (dropping any old one if needed) for given
	# database fields refs is an array # of (col, tab, tabcol) col -> tab.tabcol
	# foreign keys
	def recreate_table(self, refs):
		query = "CREATE TABLE " + self._table + " ("
		query += ','.join([ str.join(" ", k) for k in self._fields ])
		# XXX For now, assume that if an item doesn't have a sys_name, it doesn't really exist
		for (col, tab, tabcol) in refs:
			query += ", FOREIGN KEY (" + col + ") REFERENCES " + tab + " (" + tabcol + ")"
		query += ')'
		self._cursor.execute('DROP TABLE IF EXISTS ' + self._table + ' CASCADE')
		self._cursor.execute(query)

	# inserts values, returns True on success
	def insert_fields(self, values):
		try:
			sqlf = [ ]
			for fld in self._fields:
				sqlf.append(values[fld[0]])
			self._cursor.execute(self._ins_query, tuple(sqlf))
		except DBConnection.IntegrityError:
			self._cursor.conn().rollback()
			return False
		return True

# vim:set ts=2 sw=2:
