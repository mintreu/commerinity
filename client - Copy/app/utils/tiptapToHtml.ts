/**
 * TipTap/ProseMirror JSON to HTML converter
 * Converts Filament's RichEditor JSON output to HTML for frontend rendering
 */

interface TipTapMark {
  type: string
  attrs?: Record<string, unknown>
}

interface TipTapNode {
  type: string
  attrs?: Record<string, unknown>
  content?: TipTapNode[]
  text?: string
  marks?: TipTapMark[]
}

interface TipTapDoc {
  type: 'doc'
  content: TipTapNode[]
}

/**
 * Apply text marks (bold, italic, etc.) to text content
 */
function applyMarks(text: string, marks?: TipTapMark[]): string {
  if (!marks || marks.length === 0) return escapeHtml(text)

  let result = escapeHtml(text)

  for (const mark of marks) {
    switch (mark.type) {
      case 'bold':
        result = `<strong>${result}</strong>`
        break
      case 'italic':
        result = `<em>${result}</em>`
        break
      case 'underline':
        result = `<u>${result}</u>`
        break
      case 'strike':
        result = `<s>${result}</s>`
        break
      case 'code':
        result = `<code>${result}</code>`
        break
      case 'link':
        const href = mark.attrs?.href || '#'
        const target = mark.attrs?.target || '_blank'
        result = `<a href="${escapeHtml(String(href))}" target="${target}" rel="noopener noreferrer">${result}</a>`
        break
      case 'highlight':
        const color = mark.attrs?.color || 'yellow'
        result = `<mark style="background-color: ${color}">${result}</mark>`
        break
      case 'textStyle':
        const styles: string[] = []
        if (mark.attrs?.color) styles.push(`color: ${mark.attrs.color}`)
        if (mark.attrs?.fontSize) styles.push(`font-size: ${mark.attrs.fontSize}`)
        if (styles.length > 0) {
          result = `<span style="${styles.join('; ')}">${result}</span>`
        }
        break
      case 'subscript':
        result = `<sub>${result}</sub>`
        break
      case 'superscript':
        result = `<sup>${result}</sup>`
        break
    }
  }

  return result
}

/**
 * Escape HTML special characters
 */
function escapeHtml(text: string): string {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
}

/**
 * Get text alignment style
 */
function getAlignStyle(attrs?: Record<string, unknown>): string {
  if (!attrs?.textAlign || attrs.textAlign === 'start' || attrs.textAlign === 'left') {
    return ''
  }
  return ` style="text-align: ${attrs.textAlign}"`
}

/**
 * Render a single node to HTML
 */
function renderNode(node: TipTapNode): string {
  switch (node.type) {
    case 'text':
      return applyMarks(node.text || '', node.marks)

    case 'paragraph':
      const pAlign = getAlignStyle(node.attrs)
      const pContent = node.content?.map(renderNode).join('') || ''
      return `<p${pAlign}>${pContent}</p>`

    case 'heading':
      const level = node.attrs?.level || 1
      const hAlign = getAlignStyle(node.attrs)
      const hContent = node.content?.map(renderNode).join('') || ''
      return `<h${level}${hAlign}>${hContent}</h${level}>`

    case 'bulletList':
      const ulContent = node.content?.map(renderNode).join('') || ''
      return `<ul>${ulContent}</ul>`

    case 'orderedList':
      const start = node.attrs?.start ? ` start="${node.attrs.start}"` : ''
      const olContent = node.content?.map(renderNode).join('') || ''
      return `<ol${start}>${olContent}</ol>`

    case 'listItem':
      const liContent = node.content?.map(renderNode).join('') || ''
      return `<li>${liContent}</li>`

    case 'blockquote':
      const bqContent = node.content?.map(renderNode).join('') || ''
      return `<blockquote>${bqContent}</blockquote>`

    case 'codeBlock':
      const language = node.attrs?.language || ''
      const codeContent = node.content?.map(n => n.text || '').join('') || ''
      return `<pre><code class="language-${language}">${escapeHtml(codeContent)}</code></pre>`

    case 'horizontalRule':
      return '<hr>'

    case 'hardBreak':
      return '<br>'

    case 'image':
      const src = node.attrs?.src || ''
      const alt = node.attrs?.alt || ''
      const title = node.attrs?.title || ''
      const width = node.attrs?.width ? ` width="${node.attrs.width}"` : ''
      const height = node.attrs?.height ? ` height="${node.attrs.height}"` : ''
      const titleAttr = title ? ` title="${escapeHtml(String(title))}"` : ''
      return `<img src="${escapeHtml(String(src))}" alt="${escapeHtml(String(alt))}"${titleAttr}${width}${height}>`

    case 'table':
      const tableContent = node.content?.map(renderNode).join('') || ''
      return `<table>${tableContent}</table>`

    case 'tableRow':
      const trContent = node.content?.map(renderNode).join('') || ''
      return `<tr>${trContent}</tr>`

    case 'tableHeader':
      const thAlign = getAlignStyle(node.attrs)
      const colspan = node.attrs?.colspan && node.attrs.colspan > 1 ? ` colspan="${node.attrs.colspan}"` : ''
      const rowspan = node.attrs?.rowspan && node.attrs.rowspan > 1 ? ` rowspan="${node.attrs.rowspan}"` : ''
      const colwidth = node.attrs?.colwidth ? ` style="width: ${Array.isArray(node.attrs.colwidth) ? node.attrs.colwidth[0] : node.attrs.colwidth}px"` : ''
      const thContent = node.content?.map(renderNode).join('') || ''
      return `<th${colspan}${rowspan}${colwidth || thAlign}>${thContent}</th>`

    case 'tableCell':
      const tdAlign = getAlignStyle(node.attrs)
      const tdColspan = node.attrs?.colspan && node.attrs.colspan > 1 ? ` colspan="${node.attrs.colspan}"` : ''
      const tdRowspan = node.attrs?.rowspan && node.attrs.rowspan > 1 ? ` rowspan="${node.attrs.rowspan}"` : ''
      const tdColwidth = node.attrs?.colwidth ? ` style="width: ${Array.isArray(node.attrs.colwidth) ? node.attrs.colwidth[0] : node.attrs.colwidth}px"` : ''
      const tdContent = node.content?.map(renderNode).join('') || ''
      return `<td${tdColspan}${tdRowspan}${tdColwidth || tdAlign}>${tdContent}</td>`

    case 'details':
      const detailsContent = node.content?.map(renderNode).join('') || ''
      return `<details>${detailsContent}</details>`

    case 'detailsSummary':
      const summaryContent = node.content?.map(renderNode).join('') || ''
      return `<summary>${summaryContent}</summary>`

    case 'detailsContent':
      return node.content?.map(renderNode).join('') || ''

    default:
      // For unknown nodes, try to render their content
      if (node.content) {
        return node.content.map(renderNode).join('')
      }
      return ''
  }
}

/**
 * Check if a string is TipTap JSON
 */
export function isTipTapJson(content: string): boolean {
  if (!content || typeof content !== 'string') return false

  try {
    const parsed = JSON.parse(content)
    return parsed && typeof parsed === 'object' && parsed.type === 'doc' && Array.isArray(parsed.content)
  } catch {
    return false
  }
}

/**
 * Convert TipTap JSON to HTML
 */
export function tiptapToHtml(json: string | TipTapDoc): string {
  try {
    const doc: TipTapDoc = typeof json === 'string' ? JSON.parse(json) : json

    if (!doc || doc.type !== 'doc' || !Array.isArray(doc.content)) {
      return typeof json === 'string' ? json : ''
    }

    return doc.content.map(renderNode).join('')
  } catch {
    // If parsing fails, return the original string (might be HTML already)
    return typeof json === 'string' ? json : ''
  }
}

/**
 * Convert content to HTML - handles both TipTap JSON and plain HTML
 */
export function contentToHtml(content: string): string {
  if (!content) return ''

  if (isTipTapJson(content)) {
    return tiptapToHtml(content)
  }

  // Already HTML or plain text
  return content
}
