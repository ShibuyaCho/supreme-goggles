// Universal Modal Keyboard Handler
// This script automatically adds keyboard functionality to all modals in the application

document.addEventListener('DOMContentLoaded', function() {
    // Define modal selectors and their keyboard behavior
    const modalConfigs = {
        // Customer modals
        'showCustomerModal': { escape: () => window.Alpine.getState().showCustomerModal = false },
        'showEditCustomerModal': { escape: 'closeEditCustomerModal' },
        'showAddCustomerModal': { escape: 'closeAddCustomerModal' },
        'showCustomerViewModal': { escape: () => window.Alpine.getState().showCustomerViewModal = false },

        // Product modals
        'showMetrcModal': { escape: () => window.Alpine.getState().showMetrcModal = false },
        'showTransferModal': { escape: () => window.Alpine.getState().showTransferModal = false },
        'showEditModal': { escape: () => window.Alpine.getState().showEditModal = false },
        'showAddProductModal': { escape: () => window.Alpine.getState().showAddProductModal = false },
        'showMetrcImportModal': { escape: () => window.Alpine.getState().showMetrcImportModal = false },
        'showVendorPackagesModal': { escape: () => window.Alpine.getState().showVendorPackagesModal = false },
        'showAgingModal': { escape: () => window.Alpine.getState().showAgingModal = false },

        // Print modals
        'showPrintTypeModal': { escape: () => window.Alpine.getState().showPrintTypeModal = false },
        'showPrintPreviewModal': { escape: 'cancelPrintPreview' },
        'showPrintSettingsPreviewModal': { escape: 'closePrintSettingsPreview' },
        'showPrintModal': { escape: () => window.Alpine.getState().showPrintModal = false },

        // Payment modals
        'showCashModal': { escape: () => window.Alpine.getState().showCashModal = false },
        'showDebitModal': { escape: () => window.Alpine.getState().showDebitModal = false },

        // Sales modals
        'showNewSaleModal': { escape: () => window.Alpine.getState().showNewSaleModal = false },
        'showRecreationalModal': { escape: 'cancelNewSale' },
        'showMedicalModal': { escape: 'cancelNewSale' },
        'showSaleDetailsModal': { escape: () => window.Alpine.getState().showSaleDetailsModal = false },
        'showVoidSaleModal': { escape: () => window.Alpine.getState().showVoidSaleModal = false },

        // Employee modals
        'showAddEmployeeModal': { escape: () => window.Alpine.getState().showAddEmployeeModal = false },
        'showEmployeeModal': { escape: () => window.Alpine.getState().showEmployeeModal = false },
        'showPinModal': { escape: () => window.Alpine.getState().showPinModal = false },
        'showEmployeeAssignModal': { escape: () => window.Alpine.getState().showEmployeeAssignModal = false },
        'showCashCountModal': { escape: () => window.Alpine.getState().showCashCountModal = false },

        // Room and Drawer modals
        'showAddRoomModal': { escape: 'closeAddRoomModal' },
        'showRoomDetailsModal': { escape: () => window.Alpine.getState().showRoomDetailsModal = false },
        'showAddDrawerModal': { escape: () => window.Alpine.getState().showAddDrawerModal = false },

        // Settings modals
        'showAddTierModal': { escape: 'closeTierModal' },
        'showCreateDealModal': { escape: 'closeCreateDealModal' },

        // Loyalty modals
        'showEnrollCustomerModal': { escape: () => window.Alpine.getState().showEnrollCustomerModal = false },
        'showAdjustPointsModal': { escape: () => window.Alpine.getState().showAdjustPointsModal = false },

        // Discount modals
        'showDiscountModal': { escape: () => window.Alpine.getState().showDiscountModal = false },
        'showItemDiscountModal': { escape: () => window.Alpine.getState().showItemDiscountModal = false },
    };

    // Enhanced keyboard event handler
    function handleModalKeyboard(event) {
        // Only handle if Alpine.js is available
        if (!window.Alpine) return;

        const alpineData = window.Alpine.store('cannabisPOS') || {};
        
        // Check which modal is currently open
        for (const [modalName, config] of Object.entries(modalConfigs)) {
            if (alpineData[modalName]) {
                switch (event.key) {
                    case 'Escape':
                        event.preventDefault();
                        event.stopPropagation();
                        
                        if (typeof config.escape === 'function') {
                            config.escape();
                        } else if (typeof config.escape === 'string') {
                            // Call the named function if it exists
                            if (alpineData[config.escape]) {
                                alpineData[config.escape]();
                            }
                        }
                        break;
                        
                    case 'Enter':
                        // Only handle Enter if the target is not a textarea or if Ctrl/Cmd is pressed
                        if (event.target.tagName !== 'TEXTAREA' || event.ctrlKey || event.metaKey) {
                            if (config.enter) {
                                event.preventDefault();
                                event.stopPropagation();
                                
                                if (typeof config.enter === 'function') {
                                    config.enter();
                                } else if (typeof config.enter === 'string') {
                                    if (alpineData[config.enter]) {
                                        alpineData[config.enter]();
                                    }
                                }
                            }
                        }
                        break;
                }
                break; // Only handle the first open modal
            }
        }
    }

    // Add global keyboard event listener
    document.addEventListener('keydown', handleModalKeyboard);
    
    console.log('🎹 Universal modal keyboard handler initialized');
});
