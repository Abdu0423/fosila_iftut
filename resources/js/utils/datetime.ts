const ZONE = 'Asia/Dushanbe'
const NativeDate = Date

function hasZone(value: string): boolean {
  return /(?:[zZ]|[+-]\d{2}:?\d{2})$/.test(value)
}

function fromValue(value: unknown): Date {
  if (typeof value !== 'string') {
    return new NativeDate(value as never)
  }

  const text = value.trim()
  if (/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/.test(text) && !hasZone(text)) {
    return new NativeDate(`${text.replace(' ', 'T').replace(/\.\d+$/, '')}Z`)
  }

  return new NativeDate(text)
}

function AppDate(this: unknown, ...args: unknown[]): Date {
  if (args.length === 1) {
    return fromValue(args[0])
  }

  return new (NativeDate as unknown as new (...input: unknown[]) => Date)(...args)
}

AppDate.prototype = NativeDate.prototype
Object.setPrototypeOf(AppDate, NativeDate)
AppDate.now = NativeDate.now
AppDate.parse = (value: string) => fromValue(value).getTime()
AppDate.UTC = NativeDate.UTC
globalThis.Date = AppDate as DateConstructor

function withZone(options?: Intl.DateTimeFormatOptions): Intl.DateTimeFormatOptions {
  return { ...options, timeZone: ZONE }
}

const time = NativeDate.prototype.toLocaleTimeString
const date = NativeDate.prototype.toLocaleDateString
const both = NativeDate.prototype.toLocaleString

NativeDate.prototype.toLocaleTimeString = function (locales?: Intl.LocalesArgument, options?: Intl.DateTimeFormatOptions) {
  return time.call(this, locales || 'ru-RU', withZone(options))
}

NativeDate.prototype.toLocaleDateString = function (locales?: Intl.LocalesArgument, options?: Intl.DateTimeFormatOptions) {
  return date.call(this, locales || 'ru-RU', withZone(options))
}

NativeDate.prototype.toLocaleString = function (locales?: Intl.LocalesArgument, options?: Intl.DateTimeFormatOptions) {
  return both.call(this, locales || 'ru-RU', withZone(options))
}
