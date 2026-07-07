/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./resources/views/**/*.blade.php'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"IBM Plex Sans Arabic"', 'sans-serif'],
        display: ['Tajawal', 'sans-serif'],
      },
      colors: {
        ink:    { DEFAULT: '#1B2A41', soft: '#22324d', muted: '#5b6b85' },
        paper:  '#F1F5F7',
        brass:  { DEFAULT: '#1499B0', soft: '#8AD7E4', dim: '#e2f4f7' },
        ok:     '#16a34a',
        warn:   '#d97706',
        danger: '#dc2626',
        gone:   '#6b7280',
      },
      boxShadow: {
        card: '0 1px 2px rgba(27,42,65,.06), 0 8px 24px -12px rgba(27,42,65,.18)',
      },
    },
  },
  safelist: [
    // فئات ملوّنة تُبنى ديناميكياً في لوحة المعلومات
    { pattern: /^(bg|text|border)-(ink|ok|warn|danger|gone|brass)(\/(4|5|8|10|12|20|25))?$/ },
    { pattern: /^(bg|text|border)-(blue|teal|purple|amber|red|green)-(50|100|200|600|700)(\/(4|5|10))?$/ },
  ],
};
