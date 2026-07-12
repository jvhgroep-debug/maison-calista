#!/usr/bin/env node
/**
 * Compile a GNU gettext .po file into a valid binary .mo file.
 * Usage: node compile-po.js [input.po] [output.mo]
 */
'use strict';

const fs = require('fs');
const path = require('path');

const input = process.argv[2] || path.join(__dirname, '../maison-calista-theme/languages/fr_FR.po');
const output = process.argv[3] || input.replace(/\.po$/i, '.mo');

function unescapePo(str) {
	return str
		.replace(/\\n/g, '\n')
		.replace(/\\t/g, '\t')
		.replace(/\\r/g, '\r')
		.replace(/\\"/g, '"')
		.replace(/\\\\/g, '\\');
}

function parsePo(content) {
	const entries = [];
	let msgid = null;
	let msgstr = null;
	let state = null;
	const lines = content.replace(/\r\n/g, '\n').split('\n');

	function pushEntry() {
		if (msgid !== null && msgstr !== null) {
			entries.push({ msgid: unescapePo(msgid), msgstr: unescapePo(msgstr) });
		}
		msgid = null;
		msgstr = null;
		state = null;
	}

	for (const line of lines) {
		const trimmed = line.trim();
		if (!trimmed || trimmed.startsWith('#')) continue;

		if (trimmed.startsWith('msgid ')) {
			if (msgid !== null) pushEntry();
			msgid = trimmed.slice(6).replace(/^"|"$/g, '');
			state = 'msgid';
			continue;
		}
		if (trimmed.startsWith('msgstr ')) {
			msgstr = trimmed.slice(7).replace(/^"|"$/g, '');
			state = 'msgstr';
			continue;
		}
		if (trimmed.startsWith('"')) {
			const chunk = trimmed.replace(/^"|"$/g, '');
			if (state === 'msgid') msgid += chunk;
			else if (state === 'msgstr') msgstr += chunk;
		}
	}
	pushEntry();
	return entries;
}

function compileMo(entries) {
	const originals = [];
	const translations = [];
	const table = [];

	let originalsData = Buffer.alloc(0);
	let translationsData = Buffer.alloc(0);

	for (const entry of entries) {
		const idBuf = Buffer.from(entry.msgid + '\0', 'utf8');
		const strBuf = Buffer.from(entry.msgstr + '\0', 'utf8');
		originals.push({ length: idBuf.length, offset: originalsData.length });
		originalsData = Buffer.concat([originalsData, idBuf]);
		translations.push({ length: strBuf.length, offset: translationsData.length });
		translationsData = Buffer.concat([translationsData, strBuf]);
	}

	const headerSize = 28;
	const originalsTableOffset = headerSize;
	const translationsTableOffset = originalsTableOffset + originals.length * 8;
	const originalsDataOffset = translationsTableOffset + translations.length * 8;
	const translationsDataOffset = originalsDataOffset + originalsData.length;

	for (const item of originals) {
		table.push(writeUint32(item.length), writeUint32(originalsDataOffset + item.offset));
	}
	for (const item of translations) {
		table.push(writeUint32(item.length), writeUint32(translationsDataOffset + item.offset));
	}

	const header = Buffer.alloc(headerSize);
	header.writeUInt32LE(0x950412de, 0);
	header.writeUInt32LE(0, 4);
	header.writeUInt32LE(entries.length, 8);
	header.writeUInt32LE(originalsTableOffset, 12);
	header.writeUInt32LE(translationsTableOffset, 16);
	header.writeUInt32LE(0, 20);
	header.writeUInt32LE(0, 24);

	return Buffer.concat([header, ...table, originalsData, translationsData]);
}

function writeUint32(value) {
	const buf = Buffer.alloc(4);
	buf.writeUInt32LE(value, 0);
	return buf;
}

if (!fs.existsSync(input)) {
	console.error('Input .po not found:', input);
	process.exit(1);
}

const po = fs.readFileSync(input, 'utf8');
const entries = parsePo(po);
const mo = compileMo(entries);
fs.writeFileSync(output, mo);
console.log(`Compiled ${entries.length} strings -> ${output}`);
