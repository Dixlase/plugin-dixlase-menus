/**
 * Build a compact JSON manifest of free Font Awesome icons usable by the
 * menu icon picker. Reads node_modules/@fortawesome/fontawesome-free's
 * metadata and emits one entry per icon name, deduplicated across families,
 * with the styles available under the free license and aggregated search
 * terms.
 *
 * Run from the host app root:
 *   node plugins/DixlaseMenus/scripts/generate-fa-icons.js
 */

const fs = require('fs');
const path = require('path');

const repoRoot = path.resolve(__dirname, '../../..');
const metaDir = path.join(
    repoRoot,
    'node_modules/@fortawesome/fontawesome-free/metadata'
);
const iconFamiliesPath = path.join(metaDir, 'icon-families.json');
const categoriesPath = path.join(metaDir, 'categories.yml');
const outPath = path.resolve(
    __dirname,
    '../resources/src/admin/js/data/fa-free-icons.json'
);

const STYLE_TO_PREFIX = {
    solid: 'fas',
    regular: 'far',
    brands: 'fab',
};

const families = JSON.parse(fs.readFileSync(iconFamiliesPath, 'utf8'));

const categoriesYaml = fs.readFileSync(categoriesPath, 'utf8');
const categoryByIcon = parseCategoriesYaml(categoriesYaml);

const icons = [];
for (const [name, meta] of Object.entries(families)) {
    const freeFamilies = (meta.familyStylesByLicense || {}).free || [];
    const styles = [];
    for (const entry of freeFamilies) {
        if (entry.family !== 'classic') continue;
        const prefix = STYLE_TO_PREFIX[entry.style];
        if (prefix && !styles.includes(prefix)) styles.push(prefix);
    }
    if (styles.length === 0) continue;

    icons.push({
        n: name,
        l: meta.label || name,
        s: styles,
        t: (meta.search && meta.search.terms) || [],
        c: categoryByIcon[name] || [],
    });
}

icons.sort((a, b) => a.n.localeCompare(b.n));

fs.mkdirSync(path.dirname(outPath), { recursive: true });
fs.writeFileSync(outPath, JSON.stringify(icons));

console.log(`Wrote ${icons.length} icons to ${outPath}`);
console.log(
    `File size: ${(fs.statSync(outPath).size / 1024).toFixed(1)} KB`
);

/**
 * Minimal parser for the FA categories.yml shape:
 *
 *   <category>:
 *     icons:
 *       - <name>
 *       - <name>
 *     label: <Label>
 *
 * Returns a map of icon name -> array of category keys it appears in.
 * We only read what we need; pulling in a full YAML dependency for this
 * single file is unnecessary.
 */
function parseCategoriesYaml(text) {
    const result = {};
    const lines = text.split('\n');
    let currentCategory = null;
    let inIcons = false;

    for (const raw of lines) {
        if (!raw.trim() || raw.trim().startsWith('#')) continue;

        // Top-level category key (no leading whitespace, ends with ':')
        const topMatch = raw.match(/^([a-zA-Z0-9_-]+):\s*$/);
        if (topMatch) {
            currentCategory = topMatch[1];
            inIcons = false;
            continue;
        }

        if (currentCategory) {
            if (/^\s+icons:\s*$/.test(raw)) {
                inIcons = true;
                continue;
            }
            if (/^\s+[a-zA-Z0-9_-]+:/.test(raw)) {
                inIcons = false;
                continue;
            }
            if (inIcons) {
                const itemMatch = raw.match(/^\s+-\s+(.+?)\s*$/);
                if (itemMatch) {
                    const iconName = itemMatch[1];
                    if (!result[iconName]) result[iconName] = [];
                    if (!result[iconName].includes(currentCategory)) {
                        result[iconName].push(currentCategory);
                    }
                }
            }
        }
    }

    return result;
}
