#!/usr/bin/env python3

class Configuration:
	def database():
		# returns the connect string to use with the database
		return """...""";

	def data_dir():
		# returns the location of the data files (you need to extract data.fdb to this location)
		return """.../data""";

	def xml_dir():
		# returns the location of the xml db_... files
		return """.../romdb/xml""";

# vim:set ts=2 sw=2:
