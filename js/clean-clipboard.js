/**
 * This file is part of wp-Typography.
 *
 * Copyright 2016-2020 Peter Putzer.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * ***
 *
 * @file    This file handles removing hyphenation points from the clipboard.
 * @author  Peter Putzer <github@mundschenk.at>
 * @since   3.3.0
 */

/**
     * Cleans up clipboard content on cut & copy. Removes &shy; and zero-width space.
     *
     * @since 5.7.0 Dependency on jQuery removed.
     *
     * @author Peter Putzer <github@mundschenk.at>
     */
(function () {
  'use strict';

  if (window.getSelection) {
    document.addEventListener('copy', function () {
      var sel = window.getSelection(),
      ranges = [],
      rangeCount = sel.rangeCount;

      for (var i = 0; i < rangeCount; i++) {
        ranges[i] = sel.getRangeAt(i);
      }

      // Create new div containing cleaned HTML content
      var shadow = document.createElement('div');
      shadow.appendChild(sel.getRangeAt(0).cloneContents());
      shadow.style.position = 'absolute';
      shadow.style.left = '-99999px';
      shadow.innerHTML = shadow.innerHTML
      // Remove soft-hyphens.
      .replace(/\u00AD/gi, '')
      // Also remove zero-width spaces.
      .replace(/\u200B/gi, '');

      // Append to DOM
      document.body.appendChild(shadow);

      // Select the children of our "clean" div
      sel.selectAllChildren(shadow);

      // Clean up after copy
      window.setTimeout(function () {
        // Remove div
        shadow.remove();

        // Restore selection
        sel.removeAllRanges();
        for (var _i = 0; _i < rangeCount; _i++) {
          sel.addRange(ranges[_i]);
        }
      }, 0);
    });
  }
})();
