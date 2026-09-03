/**
 * File Viewer — PDF / Word / Excel only (on-demand renderers).
 * @see https://github.com/flyfish-dev/file-viewer
 * @see https://doc.file-viewer.app/zh/guide/on-demand-renderers
 */
import { setDefaultFileViewerAssetBaseUrl } from '@file-viewer/core'
import { mountViewer } from '@file-viewer/web'
import { pdfRenderer } from '@file-viewer/renderer-pdf'
import { wordRenderer } from '@file-viewer/renderer-word'
import { spreadsheetRenderer } from '@file-viewer/renderer-spreadsheet'

const ASSET_BASE = '/build/file-viewer/'
setDefaultFileViewerAssetBaseUrl(ASSET_BASE)

const options = {
  builtinRenderers: 'none',
  rendererMode: 'replace',
  renderers: [pdfRenderer, wordRenderer, spreadsheetRenderer],
  locale: 'zh-CN',
  theme: 'light',
  toolbar: true,
  toolbarPosition: 'bottom-right',
  styleIsolation: 'none',
  docx: { worker: false },
  spreadsheet: { worker: false },
  pdf: {
    assetBaseUrl: ASSET_BASE,
    withCredentials: true,
  },
}

function showStatus(root, html, isError = false) {
  const el = document.createElement('div')
  el.className = isError ? 'file-viewer-boot-error' : 'file-viewer-boot-loading'
  el.innerHTML = html
  root.replaceChildren(el)
}

function getExtension(filename) {
  const base = String(filename || '').split(/[\\/]/).pop() || ''
  const dot = base.lastIndexOf('.')
  if (dot <= 0 || dot === base.length - 1) {
    return ''
  }
  return base.slice(dot + 1).toLowerCase()
}

function ensureFilename(filename, extension) {
  const name = String(filename || '').trim() || 'document'
  const ext = String(extension || getExtension(name) || '').toLowerCase()
  if (!ext) {
    return name
  }
  if (getExtension(name) === ext) {
    return name
  }
  return `${name}.${ext}`
}

async function fetchDocumentBuffer(url) {
  const response = await fetch(url, {
    credentials: 'same-origin',
    cache: 'no-store',
    headers: {
      Accept: '*/*',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
  if (!response.ok) {
    throw new Error(`文件下载失败（HTTP ${response.status}）`)
  }
  const buffer = await response.arrayBuffer()
  if (!buffer.byteLength) {
    throw new Error('文件内容为空')
  }
  return buffer
}

export async function bootFileViewer(root = document.getElementById('file-viewer-root')) {
  if (!root) {
    return null
  }

  const src = root.dataset.src || root.getAttribute('data-src')
  const rawFilename = root.dataset.filename || root.getAttribute('data-filename') || 'document'
  const extensionHint = (
    root.dataset.extension
    || root.getAttribute('data-extension')
    || getExtension(rawFilename)
  ).toLowerCase()
  const filename = ensureFilename(rawFilename, extensionHint)
  // File Viewer type must be extension (docx), never MIME.
  const extension = getExtension(filename)
  const downloadUrl = root.dataset.downloadUrl || root.getAttribute('data-download-url') || ''

  if (!src) {
    showStatus(root, '缺少预览文件地址', true)
    return null
  }
  if (!extension) {
    showStatus(root, '无法识别文件扩展名', true)
    return null
  }

  root.style.cssText = 'position:relative;width:100%;height:100%;min-height:100%;'
  showStatus(root, '正在加载文档预览…')

  try {
    const buffer = await fetchDocumentBuffer(new URL(src, window.location.origin).href)
    const mountNode = document.createElement('div')
    mountNode.className = 'file-viewer-mount'
    mountNode.style.cssText = 'width:100%;height:100%;min-height:100%;'
    root.replaceChildren(mountNode)

    return mountViewer(mountNode, {
      buffer,
      filename,
      name: filename,
      type: extension,
      options,
    })
  } catch (error) {
    console.error('[FileViewer]', error)
    showStatus(
      root,
      [
        `<p>无法加载预览：${error?.message || error}</p>`,
        downloadUrl ? `<p><a href="${downloadUrl}">下载原文件</a></p>` : '',
      ].join(''),
      true,
    )
    return null
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => void bootFileViewer())
} else {
  void bootFileViewer()
}

window.ProjexFileViewer = { bootFileViewer, options }
