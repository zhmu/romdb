#ifndef __TGAFILE_H__
#define __TGAFILE_H__

#include <stdint.h>

#define PACKED __attribute__((packed))

namespace TGAFile {
	/*! \brief TGA file header
	 *
	 *  Based on http://www.gamers.org/dEngine/quake3/TGA.txt
	 */
	struct Header {
		uint8_t h_idfield_numchars;
		uint8_t h_colormap_type;
		uint8_t h_imagetype_code;
#define TGA_HEADER_IMAGETYPE_COLORMAPPED	1
#define TGA_HEADER_IMAGETYPE_UNMAPPED_RGB	2
#define TGA_HEADER_IMAGETYPE_RLE_COLORMAPPED	9
#define TGA_HEADER_IMAGETYPE_RLE_RGB		10
		uint16_t h_colormap_origin;
		uint16_t h_colormap_length;
		uint8_t h_colormap_entry_size;
		uint16_t h_x_origin;
		uint16_t h_y_origin;
		uint16_t h_width_image;
		uint16_t h_height_image;
		uint8_t h_pixel_size;
		uint8_t h_image_descriptor;
#define TGA_HEADER_IMAGEDESCRIPTOR_ATTR_BITS(x) (x)
#define TGA_HEADER_IMAGEDESCRIPTOR_ORIGIN_LOWERLEFT	(0 << 5)
#define TGA_HEADER_IMAGEDESCRIPTOR_ORIGIN_UPPERLEFT	(1 << 5)
#define TGA_HEADER_IMAGEDESCRIPTOR_INTERLEAVE_NONE	(0 << 6)
#define TGA_HEADER_IMAGEDESCRIPTOR_INTERLEAVE_TWOWAY	(1 << 6)
#define TGA_HEADER_IMAGEDESCRIPTOR_INTERLEAVE_FOURWAY	(2 << 6)
	} PACKED;

};

#endif /* __TGAFILE_H__ */
