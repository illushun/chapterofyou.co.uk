import { execFileSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import ts from 'typescript';
import postcss from 'postcss';
import { parse as parseVue } from '@vue/compiler-sfc';

const files = [...new Set(execFileSync('git', ['ls-files', '--cached', '--others', '--exclude-standard', '-z'], { encoding: 'utf8' }).split('\0'))].filter(file => file && existsSync(file));
const forbidden = new RegExp([String.fromCodePoint(8212), '&' + 'mdash;', '&#' + '8212;', '&#x' + '2014;', '\\\\u' + '2014'].join('|'), 'i');
const failures = [];

function checkScript(source, name) {
    const file = ts.createSourceFile(name, source, ts.ScriptTarget.Latest, true);
    function visit(node) {
        const comments = [...(ts.getLeadingCommentRanges(source, node.pos) || []), ...(ts.getTrailingCommentRanges(source, node.end) || [])];
        if (comments.some(comment => source.slice(comment.pos, comment.end).includes('\n'))) failures.push(name + ': multiline comment');
        ts.forEachChild(node, visit);
    }
    visit(file);
}

function checkCss(source, name) {
    postcss.parse(source).walkComments(comment => {
        if (comment.text.includes('\n')) failures.push(name + ': multiline comment');
    });
}

for (const file of files) {
    const source = readFileSync(file, 'utf8');
    if (forbidden.test(source)) failures.push(file + ': em dash');
    if (/\.(?:ts|mjs|js)$/.test(file)) checkScript(source, file);
    if (file.endsWith('.css')) checkCss(source, file);
    if (file.endsWith('.vue')) {
        const { descriptor } = parseVue(source);
        for (const block of [descriptor.script, descriptor.scriptSetup].filter(Boolean)) checkScript(block.content, file);
        for (const style of descriptor.styles) checkCss(style.content, file);
    }
    if (/\.(?:vue|blade\.php)$/.test(file)) {
        for (const match of source.matchAll(/<!--[\s\S]*?-->|\{\{--[\s\S]*?--\}\}/g)) {
            if (match[0].includes('\n')) failures.push(file + ': multiline template comment');
        }
    }
}

const phpComments = JSON.parse(execFileSync('php', ['scripts/check-comments.php'], { input: JSON.stringify(files), encoding: 'utf8' }));
failures.push(...phpComments.map(file => file + ': multiline comment'));

if (failures.length) {
    console.error([...new Set(failures)].join('\n'));
    process.exitCode = 1;
} else {
    console.log('One-line comments and copy conventions passed.');
}
