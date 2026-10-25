import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const viewsDir = path.join(__dirname, '..', '..', 'resources', 'views');
const targetDir = path.join(__dirname, '..', 'views');

function getAllBladeFiles(dir, fileList = []) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat.isDirectory()) {
            getAllBladeFiles(fullPath, fileList);
        } else if (file.endsWith('.blade.php')) {
            fileList.push(fullPath);
        }
    }
    return fileList;
}

// Convert PHP array syntax: [ 'a' => 'b' ] to { 'a': 'b' } while preserving [ 1, 2, 3 ]
export function phpArrayToJs(str) {
    function transform(text) {
        let res = '';
        let i = 0;
        while (i < text.length) {
            if (text[i] === '[') {
                let depth = 1;
                let j = i + 1;
                let inQuote = false;
                let quoteChar = '';
                let hasKeyValue = false;

                while (j < text.length && depth > 0) {
                    const c = text[j];
                    const prev = text[j - 1];
                    if (!inQuote && (c === '"' || c === "'")) {
                        inQuote = true;
                        quoteChar = c;
                    } else if (inQuote && c === quoteChar && prev !== '\\') {
                        inQuote = false;
                    } else if (!inQuote) {
                        if (c === '[') depth++;
                        else if (c === ']') depth--;
                        else if (depth === 1 && c === '=' && text[j + 1] === '>') {
                            hasKeyValue = true;
                        }
                    }
                    j++;
                }

                if (depth === 0) {
                    const inner = text.slice(i + 1, j - 1);
                    const transformedInner = transform(inner);
                    if (hasKeyValue) {
                        res += '{' + transformedInner.replace(/=>/g, ':') + '}';
                    } else {
                        res += '[' + transformedInner + ']';
                    }
                    i = j;
                    continue;
                }
            }
            res += text[i];
            i++;
        }
        return res;
    }

    return transform(str);
}

// Convert PHP 8 match expressions: match($val) { 'A' => 'b', default => 'c' }
export function convertPhpMatch(str) {
    return str.replace(/match\s*\(([^)]+)\)\s*\{([^}]+)\}/g, (m, subject, body) => {
        let defaultVal = "''";
        const mapEntries = [];

        const lines = body.split('\n');
        for (const line of lines) {
            const trimmed = line.trim().replace(/,$/, '');
            if (!trimmed) continue;
            const idx = trimmed.indexOf('=>');
            if (idx === -1) continue;
            const keysPart = trimmed.slice(0, idx).trim();
            const valPart = trimmed.slice(idx + 2).trim();

            if (keysPart === 'default') {
                defaultVal = valPart;
            } else {
                const keys = keysPart.split(',').map(k => k.trim());
                for (const k of keys) {
                    mapEntries.push(`${k}: ${valPart}`);
                }
            }
        }

        return `(((v) => ({ ${mapEntries.join(', ')} })[v] || ${defaultVal})(${subject.trim()}))`;
    });
}

// Extract balanced parentheses after directiveName
function replaceDirective(str, directiveName, transformFn) {
    let index = 0;
    let result = '';
    const prefix = '@' + directiveName;
    while (index < str.length) {
        const atPos = str.indexOf(prefix, index);
        if (atPos === -1) {
            result += str.slice(index);
            break;
        }

        // Must be preceded by non-word char or start of string
        if (atPos > 0 && /[a-zA-Z0-9_]/.test(str[atPos - 1])) {
            result += str.slice(index, atPos + prefix.length);
            index = atPos + prefix.length;
            continue;
        }

        const afterPrefix = atPos + prefix.length;
        let p = afterPrefix;
        while (p < str.length && (str[p] === ' ' || str[p] === '\t')) {
            p++;
        }

        if (str[p] === '(') {
            let depth = 1;
            let parenEnd = p + 1;
            let inString = false;
            let stringChar = '';

            while (parenEnd < str.length && depth > 0) {
                const char = str[parenEnd];
                const prevChar = str[parenEnd - 1];

                if (!inString && (char === '"' || char === "'")) {
                    inString = true;
                    stringChar = char;
                } else if (inString && char === stringChar && prevChar !== '\\') {
                    inString = false;
                } else if (!inString) {
                    if (char === '(') depth++;
                    else if (char === ')') depth--;
                }
                parenEnd++;
            }

            if (depth === 0) {
                const inside = str.slice(p + 1, parenEnd - 1);
                result += str.slice(index, atPos);
                result += transformFn(inside);
                index = parenEnd;
                continue;
            }
        }

        result += str.slice(index, afterPrefix);
        index = afterPrefix;
    }
    return result;
}

export function convertPhpExprToJs(expr) {
    if (!expr) return '';
    let js = expr.trim();

    // Strip PHP static modifier
    js = js.replace(/static\s+fn/g, 'fn');
    js = js.replace(/static\s+\(/g, '(');
    js = js.replace(/static\s+\$([a-zA-Z0-9_]+)/g, 'var $1');

    // Strip PHP type casting: (float), (int), etc.
    js = js.replace(/\((?:float|int|integer|string|bool|boolean|array|object)\)\s*/g, '');

    // Convert PHP 8 match expressions
    js = convertPhpMatch(js);

    // Strip PHP namespace slashes: \Carbon\Carbon -> Carbon, \App\Models\Setting -> Setting
    js = js.replace(/\\Carbon\\Carbon::/g, 'Carbon.');
    js = js.replace(/\\Carbon\\Carbon/g, 'Carbon');
    js = js.replace(/\\App\\Models\\Setting::/g, 'Setting.');
    js = js.replace(/\\App\\Models\\/g, '');
    js = js.replace(/\\([a-zA-Z0-9_]+)::/g, '$1.');
    js = js.replace(/\\([a-zA-Z0-9_]+)/g, '$1');

    // PHP arrow functions: fn($x) => expr -> ($x) => expr
    js = js.replace(/fn\s*\(([^)]*)\)\s*=>/g, '($1) =>');

    // Convert PHP associative arrays inside expressions: [ 'a' => 'b' ] -> { 'a': 'b' }
    js = phpArrayToJs(js);

    // Arrow functions returning object literals: => { ... } -> => ({ ... })
    js = js.replace(/=>\s*(\{[\s\S]*?\})/g, '=> ($1)');

    // Elvis operator ?: -> ||
    js = js.replace(/\?\:/g, ' || ');

    // PHP nullsafe ?-> to ?.
    js = js.replace(/\?->/g, '?.');

    // Arrow syntax -> to optional chaining ?.
    js = js.replace(/->/g, '?.');

    // Static scope :: to .
    js = js.replace(/::/g, '.');

    // $errors->any() etc
    js = js.replace(/\$?errors\?\.any\(\)/g, 'errors.any()');
    js = js.replace(/\$?errors\?\.all\(\)/g, 'errors.all()');
    js = js.replace(/\$?errors\?\.has\(([^)]+)\)/g, 'errors.has($1)');
    js = js.replace(/\$?errors\?\.first\(([^)]+)\)/g, 'errors.first($1)');

    // Strip json_encode flags: JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    js = js.replace(/,\s*JSON_[A-Z_|\s]+/g, '');

    // $var -> var
    js = js.replace(/\$([a-zA-Z0-9_]+)/g, '$1');

    // Dot string concatenation to + (outside quotes)
    js = js.replace(/\s+\.\s+/g, ' + ');
    js = js.replace(/([a-zA-Z0-9_\)\?\]])\.(['"])/g, '$1 + $2');
    js = js.replace(/(['"])\.([a-zA-Z0-9_\(\$])/g, '$1 + $2');

    // Prevent local variable shadowing of global route helper
    js = js.replace(/\broute\s*\(/g, '(globalThis.routeHelper || route)(');

    return js;
}

export function compileBladeToEjs(content, relPath) {
    let result = content;

    // Remove Blade server-side comments: {{-- ... --}}
    result = result.replace(/\{\{--([\s\S]*?)--\}\}/g, '');

    // Strip @props(...) directive
    result = replaceDirective(result, 'props', () => '');

    // Strip @once ... @endonce
    result = result.replace(/@once/g, '');
    result = result.replace(/@endonce/g, '');

    // Strip $attributes->merge(...)
    result = result.replace(/\{\{\s*\$attributes\?\.merge\(\[([\s\S]*?)\]\)\s*\}\}/g, '');
    result = result.replace(/\{\{\s*\$attributes([\s\S]*?)\}\}/g, '');

    // Detect layout
    let layoutType = null;
    if (result.includes("@extends('layouts.app')")) {
        layoutType = 'app';
    } else if (result.includes("@extends('layouts.admin')")) {
        layoutType = 'admin';
    } else if (result.includes("@extends('layouts.customer')")) {
        layoutType = 'customer';
    }

    // Extract title if defined via @section('title', '...')
    let titleVal = '';
    const titleMatch = result.match(/@section\(['"](?:title|page_title)['"],\s*['"](.*?)['"]\)/);
    if (titleMatch) {
        titleVal = titleMatch[1];
    }

    // Remove @extends
    result = result.replace(/@extends\(['"][^'"]+['"]\)/g, '');

    // Remove @section('title', ...)
    result = result.replace(/@section\(['"](?:title|page_title)['"],\s*['"][^'"]+['"]\)/g, '');

    // Strip content section wrapper
    result = result.replace(/@section\(['"]content['"]\)/g, '');
    result = result.replace(/@endsection/g, '');

    // Convert custom component icons: <x-icon name="..." ... />
    result = result.replace(/<x-icon\s+name=['"]([a-zA-Z0-9_\-]+)['"]([^>]*)\/>/g, (match, name, rest) => {
        let size = 20;
        let cls = '';
        let style = '';

        const sizeMatch = rest.match(/size=['"]?(\d+)['"]?/);
        if (sizeMatch) size = sizeMatch[1];

        const classMatch = rest.match(/class=['"]([^'"]+)['"]/);
        if (classMatch) cls = classMatch[1];

        const styleMatch = rest.match(/style=['"]([^'"]+)['"]/);
        if (styleMatch) style = styleMatch[1];

        return `<%- icon('${name}', { size: ${size}, class: '${cls}', style: '${style}' }) %>`;
    });

    result = result.replace(/<x-icon\.([a-zA-Z0-9_\-]+)([^>]*)\/>/g, (match, name, rest) => {
        let size = 20;
        let cls = '';
        const sizeMatch = rest.match(/size=['"]?(\d+)['"]?/);
        if (sizeMatch) size = sizeMatch[1];
        const classMatch = rest.match(/class=['"]([^'"]+)['"]/);
        if (classMatch) cls = classMatch[1];
        return `<%- icon('${name}', { size: ${size}, class: '${cls}' }) %>`;
    });

    // @csrf
    result = result.replace(/@csrf/g, '<%- csrfField() %>');

    // @method('...')
    result = result.replace(/@method\(['"]([A-Z]+)['"]\)/g, '<input type="hidden" name="_method" value="$1">');

    // @auth / @guest
    result = result.replace(/@auth/g, '<% if (isAuthenticated) { %>');
    result = result.replace(/@endauth/g, '<% } %>');
    result = result.replace(/@guest/g, '<% if (!isAuthenticated) { %>');
    result = result.replace(/@endguest/g, '<% } %>');

    // @forelse using balanced parenthesis replacement (without declaring conflicting intermediate vars)
    result = replaceDirective(result, 'forelse', (inside) => {
        const asPair = inside.match(/^\s*([\s\S]+?)\s+as\s+\$?([a-zA-Z0-9_]+)\s*=>\s*\$?([a-zA-Z0-9_]+)\s*$/);
        if (asPair) {
            const coll = convertPhpExprToJs(asPair[1]);
            const key = asPair[2];
            const val = asPair[3];
            return `<% if ((${coll}) && (Array.isArray(${coll}) ? (${coll}).length > 0 : Object.keys(${coll}).length > 0)) { (Array.isArray(${coll}) ? (${coll}).map((v, i) => [i, v]) : Object.entries(${coll})).forEach(([${key}, ${val}], _idx, _arr) => { const loop = { index: _idx, iteration: _idx + 1, first: _idx === 0, last: _idx === _arr.length - 1, count: _arr.length }; %>`;
        }
        const asSingle = inside.match(/^\s*([\s\S]+?)\s+as\s+\$?([a-zA-Z0-9_]+)\s*$/);
        if (asSingle) {
            const coll = convertPhpExprToJs(asSingle[1]);
            const val = asSingle[2];
            return `<% if ((${coll}) && (Array.isArray(${coll}) ? (${coll}).length > 0 : Object.keys(${coll}).length > 0)) { (Array.isArray(${coll}) ? (${coll}) : Object.values(${coll})).forEach((${val}, _idx, _arr) => { const loop = { index: _idx, iteration: _idx + 1, first: _idx === 0, last: _idx === _arr.length - 1, count: _arr.length }; %>`;
        }
        return `<% /* forelse error: ${inside} */ %>`;
    });

    // @empty with parentheses vs without parentheses
    result = replaceDirective(result, 'empty', (inside) => `<% if (!${convertPhpExprToJs(inside)} || (Array.isArray(${convertPhpExprToJs(inside)}) && ${convertPhpExprToJs(inside)}.length === 0)) { %>`);
    // Standalone @empty (part of @forelse)
    result = result.replace(/@empty\b(?!\s*\()/g, '<% }); } else { %>');
    result = result.replace(/@endforelse/g, '<% } %>');

    // @if, @elseif, @unless, @isset
    result = replaceDirective(result, 'if', (inside) => `<% if (${convertPhpExprToJs(inside)}) { %>`);
    result = replaceDirective(result, 'elseif', (inside) => `<% } else if (${convertPhpExprToJs(inside)}) { %>`);
    result = replaceDirective(result, 'unless', (inside) => `<% if (!(${convertPhpExprToJs(inside)})) { %>`);
    result = replaceDirective(result, 'isset', (inside) => `<% if (typeof ${convertPhpExprToJs(inside)} !== 'undefined' && ${convertPhpExprToJs(inside)} !== null) { %>`);

    result = result.replace(/@else/g, '<% } else { %>');
    result = result.replace(/@endif/g, '<% } %>');
    result = result.replace(/@endunless/g, '<% } %>');
    result = result.replace(/@endisset/g, '<% } %>');
    result = result.replace(/@endempty/g, '<% } %>');

    // @foreach using balanced parenthesis replacement
    result = replaceDirective(result, 'foreach', (inside) => {
        const asPair = inside.match(/^\s*([\s\S]+?)\s+as\s+\$?([a-zA-Z0-9_]+)\s*=>\s*\$?([a-zA-Z0-9_]+)\s*$/);
        if (asPair) {
            const coll = convertPhpExprToJs(asPair[1]);
            const key = asPair[2];
            const val = asPair[3];
            return `<% (Object.entries((${coll}) || {})).forEach(([${key}, ${val}], _idx, _arr) => { const loop = { index: _idx, iteration: _idx + 1, first: _idx === 0, last: _idx === _arr.length - 1, count: _arr.length }; %>`;
        }
        const asSingle = inside.match(/^\s*([\s\S]+?)\s+as\s+\$?([a-zA-Z0-9_]+)\s*$/);
        if (asSingle) {
            const coll = convertPhpExprToJs(asSingle[1]);
            const val = asSingle[2];
            return `<% ((${coll}) || []).forEach((${val}, _idx, _arr) => { const loop = { index: _idx, iteration: _idx + 1, first: _idx === 0, last: _idx === _arr.length - 1, count: _arr.length }; %>`;
        }
        return `<% /* foreach error: ${inside} */ %>`;
    });
    result = result.replace(/@endforeach/g, '<% }); %>');

    // @for using balanced parenthesis replacement
    result = replaceDirective(result, 'for', (inside) => {
        return `<% for (let ${convertPhpExprToJs(inside)}) { %>`;
    });
    result = result.replace(/@endfor/g, '<% } %>');

    // @continue / @break
    result = result.replace(/@continue/g, '<% continue; %>');
    result = result.replace(/@break/g, '<% break; %>');

    // Form helpers: @selected, @checked, @disabled, @readonly
    result = replaceDirective(result, 'selected', (inside) => `<%= (${convertPhpExprToJs(inside)}) ? 'selected' : '' %>`);
    result = replaceDirective(result, 'checked', (inside) => `<%= (${convertPhpExprToJs(inside)}) ? 'checked' : '' %>`);
    result = replaceDirective(result, 'disabled', (inside) => `<%= (${convertPhpExprToJs(inside)}) ? 'disabled' : '' %>`);
    result = replaceDirective(result, 'readonly', (inside) => `<%= (${convertPhpExprToJs(inside)}) ? 'readonly' : '' %>`);

    // @json
    result = replaceDirective(result, 'json', (inside) => `<%- JSON.stringify(${convertPhpExprToJs(inside)}) %>`);

    // @php(...) single-line statement
    result = replaceDirective(result, 'php', (inside) => {
        let code = convertPhpExprToJs(inside);
        if (!/^\s*(?:var|let|const)\s+/.test(code)) {
            code = 'var ' + code;
        }
        return `<% ${code}; %>`;
    });

    // @php ... @endphp
    result = result.replace(/@php([\s\S]*?)@endphp/g, (m, phpCode) => {
        let code = convertPhpExprToJs(phpCode);
        // Only declare var at statement boundaries and not if already preceded by var/let/const
        code = code.replace(/(^|;|\n)\s*(?!(?:var|let|const|if|else|return)\s+)([a-zA-Z0-9_]+)\s*=(?!=)/g, (match, pfx, vname) => {
            return `${pfx} var ${vname} =`;
        });
        return `<% ${code} %>`;
    });

    // @error('field') ... @enderror
    result = result.replace(/@error\(['"]([a-zA-Z0-9_]+)['"]\)([\s\S]*?)@enderror/g, (m, field, inner) => {
        return `<% if (typeof errors !== 'undefined' && errors && errors['${field}']) { const message = errors['${field}']; %>${inner}<% } %>`;
    });

    // @push / @endpush
    result = result.replace(/@push\(['"]scripts['"]\)/g, '');
    result = result.replace(/@endpush/g, '');
    result = result.replace(/@push\(['"]styles['"]\)/g, '');
    result = result.replace(/@push\(['"]schema['"]\)/g, '');

    // Inclusions: @include('path', [...])
    const currentDepth = relPath.split(/[\\\/]/).length - 1;
    const includeUpPrefix = currentDepth > 0 ? '../'.repeat(currentDepth) : './';

    result = replaceDirective(result, 'include', (inside) => {
        const parts = inside.split(/,\s*(.+)/);
        const templatePath = parts[0].trim().replace(/^['"]|['"]$/g, '').replace(/\./g, '/');
        const finalPath = `${includeUpPrefix}${templatePath}`;
        if (parts[1]) {
            const dataObj = convertPhpExprToJs(parts[1]);
            return `<%- include('${finalPath}', ${dataObj}) %>`;
        }
        return `<%- include('${finalPath}') %>`;
    });

    // {!! expr !!} (unescaped)
    result = result.replace(/\{!!\s*([\s\S]*?)\s*!!\}/g, (m, expr) => `<%- ${convertPhpExprToJs(expr)} %>`);

    // {{ expr }} (escaped)
    result = result.replace(/\{\{\s*([\s\S]*?)\s*\}\}/g, (m, expr) => `<%= ${convertPhpExprToJs(expr)} %>`);

    // If template has layout, wrap it with header & footer includes
    if (layoutType) {
        const depth = relPath.split(/[\\\/]/).length - 1;
        const upPrefix = depth > 0 ? '../'.repeat(depth) : './';
        let headerCode = `<% let pageTitle = '${titleVal.replace(/'/g, "\\'")}'; %>\n<%- include('${upPrefix}layouts/${layoutType}_header') %>\n`;
        let footerCode = `\n<%- include('${upPrefix}layouts/${layoutType}_footer') %>\n`;
        result = headerCode + result + footerCode;
    }

    return result;
}

export function compileAll() {
    const files = getAllBladeFiles(viewsDir);
    console.log(`Found ${files.length} blade templates to compile to EJS...`);

    let successCount = 0;
    for (const file of files) {
        const rel = path.relative(viewsDir, file);
        const relEjs = rel.replace(/\.blade\.php$/, '.ejs');
        const dest = path.join(targetDir, relEjs);

        const destDir = path.dirname(dest);
        if (!fs.existsSync(destDir)) {
            fs.mkdirSync(destDir, { recursive: true });
        }

        const bladeContent = fs.readFileSync(file, 'utf8');
        const ejsContent = compileBladeToEjs(bladeContent, rel);
        fs.writeFileSync(dest, ejsContent, 'utf8');
        successCount++;
    }

    console.log(`Successfully compiled ${successCount} templates to ${targetDir}`);
}

compileAll();
