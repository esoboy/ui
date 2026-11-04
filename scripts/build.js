/*
 * Bundles the package's JavaScript into dist/, which the asset route serves and
 * vendor:publish --tag=ui-assets copies to public/vendor/ui.
 *
 *   npm run build   minified bundles, the Emoji Picker's data, their .gz/.br, and manifest.json
 *   npm run dev     unminified, rebuilt on every change
 */

import * as esbuild from 'esbuild'
import { createHash } from 'node:crypto'
import { mkdir, readdir, readFile, rm, writeFile } from 'node:fs/promises'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { brotliCompressSync, constants, gzipSync } from 'node:zlib'

const watch = process.argv.includes('--watch')
const root = fileURLToPath(new URL('..', import.meta.url))
const dist = join(root, 'dist')

const bundles = [
    // The components shadcn has, as one classic script for @uiScripts.
    { entryPoints: { ui: 'resources/js/ui.js' }, format: 'iife' },

    // The others, and the heavy ones: modules their views load through @assets, with
    // what they share or import on demand (a Shiki language, …) split into chunks/.
    {
        entryPoints: {
            extras: 'resources/js/extras.js',
            'code-block': 'resources/js/heavy/code-block.js',
            editor: 'resources/js/heavy/editor.js',
            signature: 'resources/js/heavy/signature.js',
        },
        format: 'esm',
        splitting: true,
        chunkNames: 'chunks/[name]-[hash]',
    },
]

// The Emoji Picker's data: Emojibase's emojis in these languages.
const emojiLocales = ['en', 'zh']

// Writes a file with its compressed copies.
async function write(path, contents) {
    await mkdir(dirname(path), { recursive: true })
    await writeFile(path, contents)
    await writeFile(`${path}.gz`, gzipSync(contents, { level: 9 }))
    await writeFile(`${path}.br`, brotliCompressSync(contents, { params: { [constants.BROTLI_PARAM_QUALITY]: 11 } }))
}

// Writes a build's files, then lists every dist file with its version.
const output = {
    name: 'dist',
    setup(build) {
        build.onEnd(async result => {
            if (result.errors.length) return

            await Promise.all(result.outputFiles.map(({ path, contents }) => write(path, contents)))

            await writeManifest()

            if (watch) console.log(`[esoboy/ui] built ${result.outputFiles.length} file(s)`)
        })
    },
}

async function writeManifest() {
    const files = (await readdir(dist, { recursive: true }))
        .map(file => file.replaceAll('\\', '/'))
        .filter(file => /\.(js|css|json)$/.test(file) && file !== 'manifest.json')
        .sort()

    const manifest = {}

    for (const file of files) {
        manifest[file] = createHash('sha256').update(await readFile(join(dist, file))).digest('hex').slice(0, 12)
    }

    await writeFile(join(dist, 'manifest.json'), `${JSON.stringify(manifest, null, 4)}\n`)
}

/*
 * dist/emoji/<locale>.json: { category: [[emoji, label, keywords, Emoji version], …] } in
 * Emojibase's order, without the skin tone and hair components. Keywords are joined with |,
 * as some hold spaces.
 */
async function writeEmoji() {
    for (const locale of emojiLocales) {
        const source = join(root, 'node_modules/emojibase-data', locale)
        const emojis = JSON.parse(await readFile(join(source, 'data.json'), 'utf8'))
        const { groups } = JSON.parse(await readFile(join(source, 'messages.json'), 'utf8'))
        const data = {}

        for (const { key, order } of groups.filter(group => group.key !== 'component')) {
            data[key] = emojis
                .filter(emoji => emoji.group === order)
                .sort((a, b) => a.order - b.order)
                .map(({ emoji, label, tags = [], version }) => [emoji, label.charAt(0).toUpperCase() + label.slice(1), tags.join('|'), version])
        }

        await write(join(dist, 'emoji', `${locale}.json`), JSON.stringify(data))
    }
}

await rm(dist, { recursive: true, force: true })
await writeEmoji()

for (const bundle of bundles) {
    const options = {
        ...bundle,
        absWorkingDir: root,
        outdir: dist,
        bundle: true,
        minify: ! watch,
        write: false,
        logLevel: 'warning',
        plugins: [output],
    }

    if (watch) {
        await (await esbuild.context(options)).watch()
    } else {
        await esbuild.build(options)
    }
}
