#ifndef __FDB_H__
#define __FDB_H__

#include <stdint.h>
#include <stdio.h> // for FILE
#include <exception>
#include <vector>
#include "fdbfile.h"

//! \brief Manages a FDB file
class FDB {
public:
	FDB();
	~FDB();

	void Load(const char* filename);

	//! \brief Exception class used to report failure
	class Exception : public std::exception {
	public:
		Exception(const char* fmt, ...) throw();
		virtual ~Exception() throw();
		virtual const char* what() const throw() { return m_message; }

	private:
		char* m_message;
	};

	//! \brief Describes a single file entry
	class Entry {
		friend class FDB;
	public:
		Entry() : m_fdb(NULL), m_filename(NULL) { }

		const char* GetName() const { return m_filename; }
		int GetType() const { return m_type; }

		int GetTextureHeight() const { return m_texture_height; }
		int GetTextureWidth() const { return m_texture_width; }
		int GetTextureMipCount() const { return m_texture_mipcount; }
		int GetTextureCompression() const { return m_texture_compression; }

		void Read(char*& data, unsigned int& length) const;

	protected:
		Entry(FDB& fdb, const char* filename, unsigned int offset, const FDBFile::File& f, const FDBFile::Texture& tex);
		void ReadZlib(char*& data, unsigned int& length) const;
		void ReadUncompressed(char*& data, unsigned int& length) const;

	private:
		// ! \brief FDB we belong to
		FDB* m_fdb;

		//! \brief Entry filename
		const char* m_filename;

		//! \brief Entry offset in file
		unsigned int m_offset;

		//! \brief Type
		int m_type;

		//! \brief Entry compressed size
		uint32_t m_compressed_size;
		uint32_t m_uncompressed_size;
		uint32_t m_block_size;

		//! \brief Compression method
		int m_compression;

		//! \brief Texture attributes
		int m_texture_height;
		int m_texture_width;
		int m_texture_mipcount;
		int m_texture_compression;
	};
	typedef std::vector<Entry> TEntryVector;

	const TEntryVector& GetEntries() const { return m_entry; }

protected:
	/*! \brief Read part of the file
	 *  \param offset Offset in bytes to start
	 *  \param buf Output buffer
	 *  \param length Length to read, in bytes
	 *  \throws Exception on failure
	 */
	void Read(uint32_t offset, void* buf, unsigned int length) const;

private:
	//! \brief Maximum filename of a single file
	static const unsigned int s_max_filename_length = 128;

	//! \brief Filename chunk
	char* m_filename;

	//! \brief File we belong to
	FILE* m_file;

	TEntryVector m_entry;
};

#endif /* __FDB_H__ */
