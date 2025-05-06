window.tabPopupHandler = function () {
    return {
        activeTab: null,
        tabs: window.ShopSparkTabPopupData || [],
        openPopup(key) {
            this.activeTab = key;
        },
        closePopup() {
            this.activeTab = null;
        },
    };
};
