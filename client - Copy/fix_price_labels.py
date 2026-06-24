from pathlib import Path
path = Path('app/components/store/ProductFilters.vue')
text = path.read_text(encoding='utf-8')
text = text.replace("Under \u0192'\u00fb500", "Under \u20b9500")
text = text.replace("\u0192'\u00fb500 - \u0192'\u00fb1000", "\u20b9500 - \u20b91000")
text = text.replace("\u0192'\u00fb1000 - \u0192'\u00fb2000", "\u20b91000 - \u20b92000")
text = text.replace("\u0192'\u00fb2000 - \u0192'\u00fb5000", "\u20b92000 - \u20b95000")
text = text.replace("Above \u0192'\u00fb5000", "Above \u20b95000")
path.write_text(text, encoding='utf-8')
