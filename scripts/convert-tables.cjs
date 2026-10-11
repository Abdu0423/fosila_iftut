const fs = require('fs')

const files = [
  'resources/js/Pages/Admin/Subjects/Index.vue',
  'resources/js/Pages/Teacher/Syllabuses/Index.vue',
  'resources/js/Pages/Grades/Index.vue',
  'resources/js/Pages/EducationDepartment/Users/Index.vue',
  'resources/js/Pages/Admin/Users/Index.vue',
  'resources/js/Pages/Admin/Tests/Index.vue',
  'resources/js/Pages/Admin/Teachers/Show.vue',
  'resources/js/Pages/Admin/Teachers/Index.vue',
  'resources/js/Pages/Admin/Syllabuses/Index.vue',
  'resources/js/Pages/Admin/Syllabi/Index.vue',
  'resources/js/Pages/Admin/Students/Show.vue',
  'resources/js/Pages/Admin/Students/Management/Index.vue',
  'resources/js/Pages/Admin/Students/Index.vue',
  'resources/js/Pages/Admin/Sms/Index.vue',
  'resources/js/Pages/Admin/Reports/Index.vue',
  'resources/js/Pages/Admin/Lessons/Index.vue',
  'resources/js/Pages/Admin/Grades/Index.vue',
  'resources/js/Pages/Admin/Courses/Index.vue',
]

function rewriteSlots(src) {
  src = src.replace(/<template\s+v-slot:item\.(\w+)="\{\s*item\s*\}">/g, '<template #cell-$1="{ row }">')
  const re = /<template #cell-[\w-]+="\{ row \}">/g
  let match
  let out = ''
  let last = 0
  while ((match = re.exec(src))) {
    const start = match.index + match[0].length
    let i = start
    let depth = 1
    while (i < src.length && depth > 0) {
      const nextOpen = src.indexOf('<template', i)
      const nextClose = src.indexOf('</template>', i)
      if (nextClose === -1) break
      if (nextOpen !== -1 && nextOpen < nextClose) {
        depth++
        i = nextOpen + 9
      } else {
        depth--
        if (depth === 0) {
          const body = src.slice(start, nextClose)
            .replace(/item\.raw\./g, 'row.')
            .replace(/\bitem\b/g, 'row')
          out += src.slice(last, start) + body
          last = nextClose
          re.lastIndex = nextClose
          break
        }
        i = nextClose + 11
      }
    }
  }
  return out + src.slice(last)
}

for (const file of files) {
  let src = fs.readFileSync(file, 'utf8')
  if (!src.includes('<v-data-table')) {
    console.log('skip', file)
    continue
  }
  if (!src.includes('import ExcelSheet')) {
    src = src.replace(/(<script setup[^>]*>\r?\n)/, "$1import ExcelSheet from '@/Components/ExcelSheet.vue'\n")
  }
  src = src.replace(/<v-data-table\b([\s\S]*?)>/g, (full, attrs) => {
    const headers = (attrs.match(/:headers="([^"]+)"/) || [])[1]
    const items = (attrs.match(/:items="([^"]+)"/) || [])[1]
    if (!headers || !items) {
      console.log('NO ATTR', file)
      return full
    }
    return `<ExcelSheet :headers="${headers}" :rows="${items}"`
  })
  src = src.replace(/<\/v-data-table>/g, '</ExcelSheet>')
  src = rewriteSlots(src)
  fs.writeFileSync(file, src)
  console.log('ok', file, 'left', (src.match(/<v-data-table/g) || []).length)
}
