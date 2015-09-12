#include "fdb.h"
#include <assert.h>
#include <stdarg.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <zlib.h>

#define u32_to_host(x) (x)
#define u64_to_host(x) (x)

FDB::Exception::Exception(const char* fmt, ...) throw()
{
	va_list va;

	// First of all, determine the length of the exception
	va_start(va, fmt);
	int length = vsnprintf(NULL, 0, fmt, va) + 1;
	va_end(va);

	// Allocate that
	m_message = (char*)malloc(length + 1);
	assert(m_message != NULL);
	m_message[length] = '\0';

	// Finally, format the message
	va_start(va, fmt);
	vsnprintf(m_message, length, fmt, va);
	va_end(va);
}

FDB::Exception::~Exception() throw()
{
	free(m_message);
}

FDB::FDB()
	: m_filename(NULL), m_file(NULL)
{
}

FDB::~FDB()
{
	if (m_file != NULL)
		fclose(m_file);
	delete[] m_filename;
}

FDB::Entry::Entry(FDB& fdb, const char* filename, unsigned int offset, const FDBFile::File& f, const FDBFile::Texture& tex)
	: m_fdb(&fdb), m_filename(filename)
{
	// Copy all fields we care about
	m_compressed_size = f.f_compressed_size;
	m_uncompressed_size = f.f_uncompressed_size;
	m_compression = f.f_compression;
	m_block_size = f.f_block_size;
	m_type = f.f_type;

	// Copy texture fields too
	m_texture_height = tex.t_height;
	m_texture_width = tex.t_width;
	m_texture_mipcount = tex.t_mipcount;
	m_texture_compression = tex.t_compression;

	// Store current offset to the file data
	m_offset = offset;

#if 0
		printf(">type %u, offset %u, name '%s' uncompr %u compr %u compression %u\n",
	   f.f_type, offset, filename,
		 f.f_uncompressed_size, f.f_compressed_size, f.f_compression);
#endif
}

void
FDB::Entry::Read(char*& data, unsigned int& length) const
{
	data = NULL; length = 0;

	switch(m_compression) {
		case FDBFILE_FILE_COMPRESSION_NONE:
			ReadUncompressed(data, length);
			break;
		case FDBFILE_FILE_COMPRESSION_ZLIB:
			ReadZlib(data, length);
			break;
		default:
			throw Exception("file '%s' uses unsupported compression type %d", m_filename, m_compression);
	}
}

void
FDB::Entry::ReadZlib(char*& data, unsigned int& length) const
{
	data = new char[m_uncompressed_size];

	z_stream strm;
	strm.zalloc = Z_NULL;
	strm.zfree = Z_NULL;
	strm.opaque = Z_NULL;
	strm.avail_in = 0;
	strm.next_in = 0;
	if (inflateInit(&strm) != Z_OK)
		throw Exception("cannot initialize zlib");

	try {
		char buffer[m_block_size];
		unsigned int cur_offset = m_offset;
		unsigned int data_offset = 0;
		unsigned int in_left = m_compressed_size;
		unsigned int out_left = m_uncompressed_size;
		while (in_left > 0) {
			unsigned int in_len = std::min(m_block_size, in_left);
			m_fdb->Read(cur_offset, buffer, in_len);

			strm.next_in = (Bytef*)buffer;
			strm.avail_in = in_len;

			strm.next_out = (Bytef*)(data + data_offset);
			strm.avail_out = out_left;

			int ret = inflate(&strm, Z_NO_FLUSH);
			if (ret != Z_OK) 
				throw Exception("zlib error %d", ret);

			int did = out_left - strm.avail_out;
			cur_offset += in_len;
			data_offset += did;
			in_left -= in_len;
			out_left -= did;
		}

		length = data_offset;
	} catch(...) {
		inflateEnd(&strm);
		delete[] data;
		data = NULL;
		throw;
	}

	// All done
	inflateEnd(&strm);
}

void
FDB::Entry::ReadUncompressed(char*& data, unsigned int& length) const
{
	unsigned int block_size = 1024;

	data = new char[m_uncompressed_size];
	try {
		char buffer[block_size];
		unsigned int cur_offset = m_offset;
		unsigned int data_offset = 0;
		unsigned int left = m_uncompressed_size;
		while (left > 0) {
			unsigned int in_len = std::min(block_size, left);
			m_fdb->Read(cur_offset, buffer, in_len);

			memcpy(((char*)data + data_offset), buffer, in_len);
			cur_offset += in_len;
			data_offset += in_len;
			left -= in_len;
		}
	} catch(...) {
		delete[] data;
		data = NULL;
		throw;
	}

	// All done
	length = m_uncompressed_size;
}


void
FDB::Read(uint32_t offset, void* buf, unsigned int length) const
{
	assert(m_file != NULL);
	fseek(m_file, offset, SEEK_SET);
	if (!fread(buf, length, 1, m_file))
		throw Exception("read error");
}

void
FDB::Load(const char* filename)
{
	assert(m_file == NULL);
	m_file = fopen(filename, "rb");
	if (m_file == NULL)
		throw Exception("cannot open file '%s'", filename);

	// Obtain file size to do sanity checks later
	fseek(m_file, 0, SEEK_END);
	unsigned int file_size = ftell(m_file);
	rewind(m_file);

	try {
		struct FDBFile::Header header;
		if (!fread(&header, sizeof(header), 1, m_file))
			throw Exception("read error");
		if (header.h_magic[0] != FDBFILE_HEADER_MAGIC0 ||
		    header.h_magic[1] != FDBFILE_HEADER_MAGIC1 ||
		    header.h_magic[2] != FDBFILE_HEADER_MAGIC2)
			throw Exception("not a fdb file (magic mismatch)");
		if (header.h_version != FDBFILE_HEADER_VERSION)
			throw Exception("version mismatch");
		header.h_num_files = u32_to_host(header.h_num_files);

		// Sanity check
		if (file_size / header.h_num_files < 1024)
			throw Exception("sanity check error (or fdb contains an excessive amount of small files)");

		// Now walk through it file for file for the first part; we use this only
		// for sanity-checking since all data is also present in the other chunk
		for (unsigned int idx = 0; idx < header.h_num_files; idx++) {
			struct FDBFile::FileHeader fh;
			if (!fread(&fh, sizeof(fh), 1, m_file))
				throw Exception("read error");

			fh.f_size = u32_to_host(fh.f_size);
			fh.f_time = u64_to_host(fh.f_time);
			fh.f_offset = u32_to_host(fh.f_offset);
			if (fh.f_offset + fh.f_size >= file_size)
				throw Exception("file %u expands beyond fdb file", idx);
		}

		// XXX Skip the filename length for now
		fseek(m_file, header.h_num_files * sizeof(uint32_t), SEEK_CUR);

		// Read the file name table
		uint32_t filename_tab_len;
		if (!fread(&filename_tab_len, sizeof(filename_tab_len), 1, m_file))
				throw Exception("read error");
		filename_tab_len = u32_to_host(filename_tab_len);

		// Sanity check
		if (filename_tab_len >= s_max_filename_length * header.h_num_files)
			throw Exception("sanity check error (or fdb contains excessively long file names)");

		// Read the filenames chunk; we'll use it later to hook up the filenames
		m_filename = new char[filename_tab_len];
		if (m_filename == NULL)
			throw Exception("out of memory");
		if (!fread(m_filename, filename_tab_len, 1, m_file))
				throw Exception("read error");

		// Pre-allocate the file entries, they will be filled below
		m_entry.resize(header.h_num_files);

		// Walk again through the files and hook up the extra information
		unsigned int filename_pos = 0;
		char filename[s_max_filename_length];
		for (unsigned int idx = 0; idx < header.h_num_files; idx++) {
			struct FDBFile::File fi;
			if (!fread(&fi, sizeof(fi), 1, m_file))
				throw Exception("read error");

			fi.f_block_size = u32_to_host(fi.f_block_size);
			fi.f_type = u32_to_host(fi.f_type);
			fi.f_compression = u32_to_host(fi.f_compression);
			fi.f_uncompressed_size = u32_to_host(fi.f_uncompressed_size);
			fi.f_time = u64_to_host(fi.f_time);
			fi.f_name_length = u32_to_host(fi.f_name_length);
			if (fi.f_name_length >= s_max_filename_length)
				throw Exception("sanity check error (or fdb entry %u contains an excessively long file name)", idx);

			if (!fread(filename, fi.f_name_length, 1, m_file))
				throw Exception("read error");

			// For some reason, filenames are stored in the filename chunk and in a
			// directory-type (perhaps for quicker lookups?). We are paranoid and
			// sanity-check that these match
			for (unsigned int i = 0; i < fi.f_name_length; i++)
				if (filename[i] != m_filename[filename_pos + i])
					throw Exception("sanity check error: filename of entry %u mismatches filename table", idx);

			// If this is a texture, there will be an extra structure of data
			FDBFile::Texture tex;
			if (fi.f_type == FDBFILE_FILE_TYPE_TEXTURE) {
				if (!fread(&tex, sizeof(tex), 1, m_file))
					throw Exception("read error");

				tex.t_compression = u32_to_host(tex.t_compression);
				tex.t_width = u32_to_host(tex.t_width);
				tex.t_height = u32_to_host(tex.t_height);
				tex.t_mipcount = u32_to_host(tex.t_mipcount);
			}

			// Entry is sane; initialize it based on the file entry
			m_entry[idx] = Entry(*this, m_filename + filename_pos, ftell(m_file), fi, tex);
			filename_pos += fi.f_name_length;

			// Skip the file data
			if (fi.f_compression == FDBFILE_FILE_COMPRESSION_NONE)
				fseek(m_file, fi.f_uncompressed_size, SEEK_CUR);
			else
				fseek(m_file, fi.f_compressed_size, SEEK_CUR);
		}
	} catch (...) {
		fclose(m_file);
		m_file = NULL;
		throw;
	}
}

/* vim:set ts=2 sw=2: */
