// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Password generation procedure for user edit form (user/editadvanced.php)
 *
 * @module     core_user/passwordgenerator
 * @package    core_user
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

export const init = () => {

    function generatePassword() {

        var alphabet = {
            lowerCaseChars: 'abcdefghijklmnopqrstuvwxyz',
            upperCaseChars: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            specialChars: '!#$%&()*+,-./:;<=>?@[]^_{|}~'
        };
        var isPutLowerCaseChar = false;
        var isPutUpperCaseChar = false;
        var isPutSpecialChar = false;

        function getRandomInt(min, max) {
            // generates random int value in interval from min to max inclusive
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        function getRandomStringChar(string) {
            return string[getRandomInt(0, string.length - 1)];
        }

        function getRandomAlphabetChar() {
            var randomSelector = getRandomInt(0, 3);
            switch (randomSelector) {
                case 0: {
                    isPutLowerCaseChar = true;
                    return getRandomStringChar(alphabet.lowerCaseChars);
                }
                case 1: {
                    isPutUpperCaseChar = true;
                    return getRandomStringChar(alphabet.upperCaseChars);
                }
                case 2: {
                    isPutSpecialChar = true;
                    return getRandomStringChar(alphabet.specialChars);
                }
                case 3: {
                    return getRandomInt(0, 9);
                }
            }
        }

        let length = getRandomInt(8, 16);
        let result = [];
        for (let i = 0; i < length; i++) {
            result.push(getRandomAlphabetChar());
        }
        if (!isPutLowerCaseChar) {
            result[getRandomInt(0, result.length - 1)] = getRandomStringChar(alphabet.lowerCaseChars);
        }
        if (!isPutUpperCaseChar) {
            result[getRandomInt(0, result.length - 1)] = getRandomStringChar(alphabet.upperCaseChars);
        }
        if (!isPutSpecialChar) {
            result[getRandomInt(0, result.length - 1)] = getRandomStringChar(alphabet.specialChars);
        }
        return result.join('');
    }

    let generatePasswordButton = document.getElementById('id_generatepasswordbutton');

    generatePasswordButton.onclick = function() {
        let wrapper = $('span[data-passwordunmask="wrapper"]');
        let input = document.getElementById('id_newpassword');
        let displayvalue = $('span[data-passwordunmask="displayvalue"]');
        let password = generatePassword();
        input.value = password;
        if (wrapper.data('unmasked')) {
            displayvalue.text(password);
        } else {
            let passwordMasked = '';
            for (let i = 0; i < password.length; i++) {
                passwordMasked += '&bull;';
            }
            let html = '<span>' + passwordMasked + '</span>';
            displayvalue.html(html);
        }
    };
};
