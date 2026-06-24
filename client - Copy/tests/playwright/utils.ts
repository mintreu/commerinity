import fs from 'node:fs'
import path from 'node:path'
import type { Page } from '@playwright/test'

const assetsDir = path.resolve(process.cwd(), '..', 'docs', 'commerinity-docs', 'assets')

export const ensureAssetsDir = () => {
  fs.mkdirSync(assetsDir, { recursive: true })
}

export const shotPath = (name: string) => {
  const file = `${name}.png`
  return path.join(assetsDir, file)
}

export const takeShot = async (page: Page, name: string) => {
  ensureAssetsDir()
  await page.screenshot({ path: shotPath(name), fullPage: true })
}

export const randomMobile = () => {
  const suffix = Math.floor(100000000 + Math.random() * 899999999)
  return `9${suffix}`
}

export const randomEmail = () => {
  const suffix = Math.floor(10000 + Math.random() * 89999)
  return `e2e${suffix}@demo.com`
}
