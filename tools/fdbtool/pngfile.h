#ifndef __PNGFILE_H__
#define __PNGFILE_H__

#include <stdint.h>

#define PACKED __attribute__((packed))

// Based on http://www.libpng.org/pub/png/spec/1.2/
namespace PNGFile {
	//! \brief PNG file signature bytes, LE format
	const uint32_t Signature1 = 0x474e5089;
	const uint32_t Signature2 = 0x0a1a0a0d;

	//! \brief Chunk layout
	struct ChunkPrefix {
		uint32_t cp_length;
		uint8_t  cp_type[4];
	};
	struct ChunkSuffix {
		uint32_t cs_crc;
	};

	//! \brief Image header
	struct IHDR {
		uint32_t ih_width;
		uint32_t ih_height;
		uint8_t	ih_bitdepth;
		uint8_t ih_colortype;
#define PNG_IHDR_COLORTYPE_GREYSCALE		0
#define PNG_IHDR_COLORTYPE_RGB			2
#define PNG_IHDR_COLORTYPE_PALETTE		3
#define PNG_IHDR_COLORTYPE_GREYSCALE_ALPHA	4
#define PNG_IHDR_COLORTYPE_RGBA			6
		uint8_t ih_compression;
#define PNG_IHDR_COMPRESSION_ZLIB		0
		uint8_t ih_filter;
#define PNG_IHDR_FILTER_METHOD_0		0
		uint8_t ih_interlace;
#define PNG_IHDR_INTERLACE_NONE			0
#define PNG_IHDR_INTERLACE_ADAM7		1
	} PACKED;

#define PNG_FILTER_NONE				0
#define PNG_FILTER_SUB				1
#define PNG_FILTER_UP				2
#define PNG_FILTER_AVERAGE			3
#define PNG_FILTER_PAETH			4
};

#endif /* __TGAFILE_H__ */
