#ifndef __BMPFILE_H__
#define __BMPFILE_H__

#include <stdint.h>

#define PACKED __attribute__((packed))

// Based on http://msdn.microsoft.com/en-us/library/dd183392(v=vs.85).aspx
namespace BMPFile {
	//! \brief File header
	struct FileHeader {
		uint16_t fh_type;
#define BMP_FILEHEADER_TTYPE ('B' | 'M' << 8)
		uint32_t fh_size;
		uint16_t fh_reserved1;
		uint16_t fh_reserved2;
		uint32_t fh_offbits;
	} PACKED;

	//! \brief Bitmap info header
	struct InfoHeader {
		uint32_t ih_size;
		uint32_t ih_width;
		uint32_t ih_height;
		uint16_t ih_planes;
		uint16_t ih_bitcount;
		uint32_t ih_compression;
#define BMP_HEADER_COMPRESSION_RGB		0
#define BMP_HEADER_COMPRESSION_RLE8		1
#define BMP_HEADER_COMPRESSION_RLE4		2
#define BMP_HEADER_COMPRESSION_BITFIELDS	3
#define BMP_HEADER_COMPRESSION_JPEG		4
#define BMP_HEADER_COMPRESSION_PNG		5
#define BMP_HEADER_COMPRESSION_CMYK		0xb
#define BMP_HEADER_COMPRESSION_CMYKRLE8		0xc
#define BMP_HEADER_COMPRESSION_CMYKRLE4		0xd
		uint32_t ih_sizeimage;
		uint32_t ih_xpelspermeter;
		uint32_t ih_ypelspermeter;
		uint32_t ih_colour_used;
		uint32_t ih_colour_important;
	} PACKED;

};

#endif /* __BMPFILE_H__ */
