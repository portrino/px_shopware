TCEFORM.tt_content.pi_flexform.pxshopware_pi1.sDEF.settings\.template.addItems {
    default = LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xlf:tt_content.pi_flexform.pxshopware_pi1.settings.template.default
}

mod.wizards.newContentElement.wizardItems.px_shopware {
    elements.pxshopware_pi1 {
        title = LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xml:tt_content.CType.pxshopware_pi1.title
        iconIdentifier = pxshopware-pi1
        params = &defVals[tt_content][CType]=pxshopware_pi1
        description = LLL:EXT:px_shopware/Resources/Private/Language/locallang_db.xml:tt_content.CType.pxshopware_pi1.description
        tt_content_defValues {
            CType = pxshopware_pi1
        }
    }
    show := addToList(pxshopware_pi1)
}