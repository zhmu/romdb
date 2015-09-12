#ifndef __FDBFILE_H__
#define __FDBFILE_H__

#include <stdint.h>

#define PACKED __attribute__((packed))

namespace FDBFile {
	//! \brief Header
	struct Header {
		uint8_t	h_version;
#define FDBFILE_HEADER_VERSION	1
		uint8_t	h_magic[3];
#define FDBFILE_HEADER_MAGIC0	'B'
#define FDBFILE_HEADER_MAGIC1	'D'
#define FDBFILE_HEADER_MAGIC2	'F'
		uint32_t h_num_files;
	} PACKED;

	//! \brief Footer
	struct Footer {
		uint32_t f_unknown;
		uint32_t f_num_files;
	} PACKED;

	//! \brief Per-file entry header
	struct FileHeader {
		uint32_t	f_size;
		uint64_t	f_time;
		uint32_t	f_offset;
	} PACKED;

	//! \brief Per-file entry
	struct File {
		uint32_t	f_block_size;
		uint32_t	f_type;
#define FDBFILE_FILE_TYPE_REGULAR	1
#define FDBFILE_FILE_TYPE_TEXTURE	2
		uint32_t	f_compression;
#define FDBFILE_FILE_COMPRESSION_NONE	0
#define FDBFILE_FILE_COMPRESSION_RLE	1
#define FDBFILE_FILE_COMPRESSION_ZLIB	3
#define FDBFILE_FILE_COMPRESSION_REDUX	4
		uint32_t	f_uncompressed_size;
		uint32_t	f_compressed_size;
		uint64_t	f_time;
		uint32_t	f_name_length;
	} PACKED;

	//! \brief Extra texture data
	struct Texture {
		uint32_t	t_compression;
#define FDBFILE_TEXTURE_COMPRESSION_NONE	4
#define FDBFILE_TEXTURE_COMPRESSION_DXT1	5
#define FDBFILE_TEXTURE_COMPRESSION_DXT1_ALPHA	6
#define FDBFILE_TEXTURE_COMPRESSION_DXT5	8
		uint32_t	t_width;
		uint32_t	t_height;
		uint32_t	t_mipcount;
	} PACKED;
};

#endif /* __FDBFILE_H__ */
