const fs = require('fs')
const { globSync } = require('fs')
const path = require('path')

function walk(dir, acc = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name)
    if (entry.isDirectory()) walk(full, acc)
    else if (entry.name.endsWith('.vue')) acc.push(full)
  }
  return acc
}

const files = walk('resources/js/Pages')
let fixed = 0
for (const file of files) {
  let src = fs.readFileSync(file, 'utf8')
  const next = src.replace(/<ExcelSheet :headers="([^"]+)" :rows="([^"]+)"(?!>)/g, '<ExcelSheet :headers="$1" :rows="$2">')
  if (next !== src) {
    fs.writeFileSync(file, next)
    fixed++
    console.log(file)
  }
}
console.log('fixed', fixed)
