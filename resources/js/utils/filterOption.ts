export function searchTokens(raw: string): string[] {
  return String(raw || '').trim().toLowerCase().split(/\s+/).filter(Boolean)
}

export function filterOption(value: unknown, query: string): boolean {
  const tokens = searchTokens(query)
  if (!tokens.length) {
    return true
  }
  const haystack = String(value ?? '').toLowerCase()
  return tokens.every((token) => haystack.includes(token))
}
