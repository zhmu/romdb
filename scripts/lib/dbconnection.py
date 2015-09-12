#!/usr/bin/env python3

import psycopg2
import config

# simple wrapper around psycopg2
class DBConnection:
	class IntegrityError(Exception):
		pass

	class Cursor:
		def __init__(self, parent):
			self._conn = parent._conn
			self._cursor = self._conn.cursor()

		def execute(self, *args):
			try:
				self._cursor.execute(*args)
			except psycopg2.IntegrityError:
				raise DBConnection.IntegrityError

		def fetchone(self):
			return self._cursor.fetchone()

		def fetchall(self):
			return self._cursor.fetchall()

		def conn(self):
			return self._conn

	def __init__(self):
		self._conn = psycopg2.connect(config.Configuration.database())

	def cursor(self):
		return DBConnection.Cursor(self)

	def commit(self):
		self._conn.commit()

	def rollback(self):
		self._conn.rollback()

	def close(self):
		self._conn.close()

# vim:set ts=2 sw=2:
