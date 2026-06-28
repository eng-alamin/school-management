/* ═══════════════════════════════════════════════════════════════════
   INVENTORY.JS — Inventory Module Language Extension
   Extends lang.js translations with inventory-specific keys
   and applies them to the DOM on language change.
═══════════════════════════════════════════════════════════════════ */

import { translations } from "./lang.js";

/* ── Inventory translation keys ── */
const inventoryTranslations = {
    en: {
        // ── Category component ──
        catThSl: "SL",
        catThName: "Name",
        catThActions: "Actions",
        catDeleteTitle: "Delete Category?",
        catDeleteMsg: "This action cannot be undone.",

        // ── Store component ──
        storeThSl: "SL",
        storeThName: "Name",
        storeThCode: "Code",
        storeThMobile: "Mobile",
        storeThAddress: "Address",
        storeThActions: "Actions",
        storeDeleteTitle: "Delete Store?",
        storeDeleteMsg: "This action cannot be undone.",

        // ── Supplier component ──
        supThSl: "SL",
        supThName: "Name",
        supThMobile: "Mobile",
        supThEmail: "Email",
        supThAddress: "Address",
        supThActions: "Actions",
        supDeleteTitle: "Delete Supplier?",
        supDeleteMsg: "This action cannot be undone.",

        // ── Unit component ──
        unitThSl: "SL",
        unitThName: "Name",
        unitThActions: "Actions",
        unitDeleteTitle: "Delete Unit?",
        unitDeleteMsg: "This action cannot be undone.",

        // ── Product component ──
        prodThSl: "SL",
        prodThName: "Name",
        prodThCode: "Code",
        prodThCategory: "Category",
        prodThPurchaseUnit: "Purchase Unit",
        prodThSalesUnit: "Sales Unit",
        prodThPurchasePrice: "Purchase Price",
        prodThSalesPrice: "Sales Price",
        prodThActions: "Actions",
        prodDeleteTitle: "Delete Product?",
        prodDeleteMsg: "This action cannot be undone.",

        // ── Purchase List component ──
        purchaseListTitle: "Purchases",
        purchaseListSubtitle:
            "Manage purchases, track orders, and monitor inventory procurement easily.",
        plistThSl: "SL",
        plistThStore: "Store",
        plistThStatus: "Status",
        plistThActions: "Actions",
        plistDeleteTitle: "Delete Purchase?",
        plistDeleteMsg:
            "This will also delete all associated items. This action cannot be undone.",

        // ── Purchase Add component ──
        purchaseAddTitle: "Add Purchase",
        purchaseAddSubtitle: "Create new purchase record",
        paddThNo: "#",
        paddThProduct: "Product",
        paddThUnitPrice: "Unit Price",
        paddThQty: "Qty",
        paddThDiscount: "Discount",
        paddThTotal: "Total",

        // ── Purchase Edit component ──
        purchaseEditTitle: "Edit Purchase",
        purchaseEditSubtitle: "Update purchase record",
        peditThNo: "#",
        peditThProduct: "Product",
        peditThUnitPrice: "Unit Price",
        peditThQty: "Qty",
        peditThDiscount: "Discount",
        peditThTotal: "Total",

        // ── Sale List component ──
        saleListTitle: "Sales",
        saleListSubtitle:
            "Manage sales, track bills, and monitor inventory sales easily.",
        slistThSl: "SL",
        slistThSaleTo: "Sale To",
        slistThPayment: "Payment",
        slistThActions: "Actions",
        slistDeleteTitle: "Delete Sale?",
        slistDeleteMsg:
            "This will also delete all associated items. This action cannot be undone.",

        // ── Sale Add component ──
        saleAddTitle: "Add Sale",
        saleAddSubtitle: "Create new sale bill record",
        saddThCategory: "Category",
        saddThProduct: "Product",
        saddThUnitPrice: "Unit Price",
        saddThQty: "Quantity",
        saddThDiscount: "Discount",
        saddThTotal: "Total Price",

        // ── Sale Edit component ──
        saleEditTitle: "Edit Sale",
        saleEditSubtitle: "Update existing sale bill record",
        seditThCategory: "Category",
        seditThProduct: "Product",
        seditThUnitPrice: "Unit Price",
        seditThQty: "Quantity",
        seditThDiscount: "Discount",
        seditThTotal: "Total Price",
    },
    bn: {
        // ── Category component ──
        catThSl: "ক্র.",
        catThName: "নাম",
        catThActions: "কার্যক্রম",
        catDeleteTitle: "বিভাগ মুছবেন?",
        catDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",

        // ── Store component ──
        storeThSl: "ক্র.",
        storeThName: "নাম",
        storeThCode: "কোড",
        storeThMobile: "মোবাইল",
        storeThAddress: "ঠিকানা",
        storeThActions: "কার্যক্রম",
        storeDeleteTitle: "স্টোর মুছবেন?",
        storeDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",

        // ── Supplier component ──
        supThSl: "ক্র.",
        supThName: "নাম",
        supThMobile: "মোবাইল",
        supThEmail: "ইমেইল",
        supThAddress: "ঠিকানা",
        supThActions: "কার্যক্রম",
        supDeleteTitle: "সরবরাহকারী মুছবেন?",
        supDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",

        // ── Unit component ──
        unitThSl: "ক্র.",
        unitThName: "নাম",
        unitThActions: "কার্যক্রম",
        unitDeleteTitle: "ইউনিট মুছবেন?",
        unitDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",

        // ── Product component ──
        prodThSl: "ক্র.",
        prodThName: "নাম",
        prodThCode: "কোড",
        prodThCategory: "বিভাগ",
        prodThPurchaseUnit: "ক্রয় ইউনিট",
        prodThSalesUnit: "বিক্রয় ইউনিট",
        prodThPurchasePrice: "ক্রয় মূল্য",
        prodThSalesPrice: "বিক্রয় মূল্য",
        prodThActions: "কার্যক্রম",
        prodDeleteTitle: "পণ্য মুছবেন?",
        prodDeleteMsg: "এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",

        // ── Purchase List component ──
        purchaseListTitle: "ক্রয়সমূহ",
        purchaseListSubtitle:
            "ক্রয় পরিচালনা করুন, অর্ডার ট্র্যাক করুন এবং সহজে ইনভেন্টরি সংগ্রহ পর্যবেক্ষণ করুন।",
        plistThSl: "ক্র.",
        plistThStore: "স্টোর",
        plistThStatus: "অবস্থা",
        plistThActions: "কার্যক্রম",
        plistDeleteTitle: "ক্রয় মুছবেন?",
        plistDeleteMsg:
            "এটি সংশ্লিষ্ট সব আইটেমও মুছে ফেলবে। এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",

        // ── Purchase Add component ──
        purchaseAddTitle: "ক্রয় যোগ করুন",
        purchaseAddSubtitle: "নতুন ক্রয় রেকর্ড তৈরি করুন",
        paddThNo: "#",
        paddThProduct: "পণ্য",
        paddThUnitPrice: "একক মূল্য",
        paddThQty: "পরিমাণ",
        paddThDiscount: "ছাড়",
        paddThTotal: "মোট",

        // ── Purchase Edit component ──
        purchaseEditTitle: "ক্রয় সম্পাদনা",
        purchaseEditSubtitle: "ক্রয় রেকর্ড আপডেট করুন",
        peditThNo: "#",
        peditThProduct: "পণ্য",
        peditThUnitPrice: "একক মূল্য",
        peditThQty: "পরিমাণ",
        peditThDiscount: "ছাড়",
        peditThTotal: "মোট",

        // ── Sale List component ──
        saleListTitle: "বিক্রয়সমূহ",
        saleListSubtitle:
            "বিক্রয় পরিচালনা করুন, বিল ট্র্যাক করুন এবং সহজে ইনভেন্টরি বিক্রয় পর্যবেক্ষণ করুন।",
        slistThSl: "ক্র.",
        slistThSaleTo: "বিক্রয় প্রাপক",
        slistThPayment: "পরিশোধ",
        slistThActions: "কার্যক্রম",
        slistDeleteTitle: "বিক্রয় মুছবেন?",
        slistDeleteMsg:
            "এটি সংশ্লিষ্ট সব আইটেমও মুছে ফেলবে। এই ক্রিয়াটি পূর্বাবস্থায় ফেরানো যাবে না।",

        // ── Sale Add component ──
        saleAddTitle: "বিক্রয় যোগ করুন",
        saleAddSubtitle: "নতুন বিক্রয় বিল রেকর্ড তৈরি করুন",
        saddThCategory: "বিভাগ",
        saddThProduct: "পণ্য",
        saddThUnitPrice: "একক মূল্য",
        saddThQty: "পরিমাণ",
        saddThDiscount: "ছাড়",
        saddThTotal: "মোট মূল্য",

        // ── Sale Edit component ──
        saleEditTitle: "বিক্রয় সম্পাদনা",
        saleEditSubtitle: "বিদ্যমান বিক্রয় বিল রেকর্ড আপডেট করুন",
        seditThCategory: "বিভাগ",
        seditThProduct: "পণ্য",
        seditThUnitPrice: "একক মূল্য",
        seditThQty: "পরিমাণ",
        seditThDiscount: "ছাড়",
        seditThTotal: "মোট মূল্য",
    },
};

/* ── Merge into shared translations object ── */
Object.assign(translations.en, inventoryTranslations.en);
Object.assign(translations.bn, inventoryTranslations.bn);

/* ── Apply inventory-specific DOM updates ── */
function applyInventoryLang(lang) {
    const t = inventoryTranslations[lang];
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    // ── Category ──
    setText("cat-th-sl", t.catThSl);
    setText("cat-th-name", t.catThName);
    setText("cat-th-actions", t.catThActions);
    setText("cat-delete-title", t.catDeleteTitle);
    setText("cat-delete-msg", t.catDeleteMsg);

    // ── Store ──
    setText("store-th-sl", t.storeThSl);
    setText("store-th-name", t.storeThName);
    setText("store-th-code", t.storeThCode);
    setText("store-th-mobile", t.storeThMobile);
    setText("store-th-address", t.storeThAddress);
    setText("store-th-actions", t.storeThActions);
    setText("store-delete-title", t.storeDeleteTitle);
    setText("store-delete-msg", t.storeDeleteMsg);

    // ── Supplier ──
    setText("sup-th-sl", t.supThSl);
    setText("sup-th-name", t.supThName);
    setText("sup-th-mobile", t.supThMobile);
    setText("sup-th-email", t.supThEmail);
    setText("sup-th-address", t.supThAddress);
    setText("sup-th-actions", t.supThActions);
    setText("sup-delete-title", t.supDeleteTitle);
    setText("sup-delete-msg", t.supDeleteMsg);

    // ── Unit ──
    setText("unit-th-sl", t.unitThSl);
    setText("unit-th-name", t.unitThName);
    setText("unit-th-actions", t.unitThActions);
    setText("unit-delete-title", t.unitDeleteTitle);
    setText("unit-delete-msg", t.unitDeleteMsg);

    // ── Product ──
    setText("prod-th-sl", t.prodThSl);
    setText("prod-th-name", t.prodThName);
    setText("prod-th-code", t.prodThCode);
    setText("prod-th-category", t.prodThCategory);
    setText("prod-th-purchase-unit", t.prodThPurchaseUnit);
    setText("prod-th-sales-unit", t.prodThSalesUnit);
    setText("prod-th-purchase-price", t.prodThPurchasePrice);
    setText("prod-th-sales-price", t.prodThSalesPrice);
    setText("prod-th-actions", t.prodThActions);
    setText("prod-delete-title", t.prodDeleteTitle);
    setText("prod-delete-msg", t.prodDeleteMsg);

    // ── Purchase List ──
    setText("purchase-list-title", t.purchaseListTitle);
    setText("purchase-list-subtitle", t.purchaseListSubtitle);
    setText("plist-th-sl", t.plistThSl);
    setText("plist-th-store", t.plistThStore);
    setText("plist-th-status", t.plistThStatus);
    setText("plist-th-actions", t.plistThActions);
    setText("plist-delete-title", t.plistDeleteTitle);
    setText("plist-delete-msg", t.plistDeleteMsg);

    // ── Purchase Add ──
    setText("purchase-add-title", t.purchaseAddTitle);
    setText("purchase-add-subtitle", t.purchaseAddSubtitle);
    setText("padd-th-no", t.paddThNo);
    setText("padd-th-product", t.paddThProduct);
    setText("padd-th-unit-price", t.paddThUnitPrice);
    setText("padd-th-qty", t.paddThQty);
    setText("padd-th-discount", t.paddThDiscount);
    setText("padd-th-total", t.paddThTotal);

    // ── Purchase Edit ──
    setText("purchase-edit-title", t.purchaseEditTitle);
    setText("purchase-edit-subtitle", t.purchaseEditSubtitle);
    setText("pedit-th-no", t.peditThNo);
    setText("pedit-th-product", t.peditThProduct);
    setText("pedit-th-unit-price", t.peditThUnitPrice);
    setText("pedit-th-qty", t.peditThQty);
    setText("pedit-th-discount", t.peditThDiscount);
    setText("pedit-th-total", t.peditThTotal);

    // ── Sale List ──
    setText("sale-list-title", t.saleListTitle);
    setText("sale-list-subtitle", t.saleListSubtitle);
    setText("slist-th-sl", t.slistThSl);
    setText("slist-th-sale-to", t.slistThSaleTo);
    setText("slist-th-payment", t.slistThPayment);
    setText("slist-th-actions", t.slistThActions);
    setText("slist-delete-title", t.slistDeleteTitle);
    setText("slist-delete-msg", t.slistDeleteMsg);

    // ── Sale Add ──
    setText("sale-add-title", t.saleAddTitle);
    setText("sale-add-subtitle", t.saleAddSubtitle);
    setText("sadd-th-category", t.saddThCategory);
    setText("sadd-th-product", t.saddThProduct);
    setText("sadd-th-unit-price", t.saddThUnitPrice);
    setText("sadd-th-qty", t.saddThQty);
    setText("sadd-th-discount", t.saddThDiscount);
    setText("sadd-th-total", t.saddThTotal);

    // ── Sale Edit ──
    setText("sale-edit-title", t.saleEditTitle);
    setText("sale-edit-subtitle", t.saleEditSubtitle);
    setText("sedit-th-category", t.seditThCategory);
    setText("sedit-th-product", t.seditThProduct);
    setText("sedit-th-unit-price", t.seditThUnitPrice);
    setText("sedit-th-qty", t.seditThQty);
    setText("sedit-th-discount", t.seditThDiscount);
    setText("sedit-th-total", t.seditThTotal);
}

// Apply on load — read directly from localStorage to avoid module timing issues
applyInventoryLang(localStorage.getItem("lang") || "en");

// Re-apply whenever lang.js switches language
document.addEventListener("langChanged", (e) => {
    applyInventoryLang(e.detail.lang);
});

export { inventoryTranslations, applyInventoryLang };
