/**
 * Bangladesh Administrative Address Cascading Dropdown Selector
 * Vanilla JavaScript implementation for KrishiHat
 */

export function initAddressSelector(containerId = 'bd-address-selector') {
    const container = document.getElementById(containerId);
    if (!container) return;

    const divisionSelect = container.querySelector('[data-geo="division"]');
    const districtSelect = container.querySelector('[data-geo="district"]');
    const upazilaSelect = container.querySelector('[data-geo="upazila"]');
    const unionSelect = container.querySelector('[data-geo="union"]');
    const urbanAreaSelect = container.querySelector('[data-geo="urban_area"]');
    const postOfficeSelect = container.querySelector('[data-geo="post_office"]');
    const postalCodeInput = container.querySelector('[data-geo="postal_code"]');
    const loadingBadge = container.querySelector('[data-geo="loading"]');

    if (!divisionSelect || !districtSelect || !upazilaSelect) return;

    // Cache to prevent duplicate network calls
    const cache = {
        districts: {},
        upazilas: {},
        unions: {},
        urbanAreas: {},
        postOffices: {},
    };

    function showLoading(msg = 'তথ্য লোড হচ্ছে...') {
        if (loadingBadge) {
            loadingBadge.textContent = '⏳ ' + msg;
            loadingBadge.classList.remove('d-none');
        }
    }

    function hideLoading() {
        if (loadingBadge) {
            loadingBadge.classList.add('d-none');
        }
    }

    function clearSelect(select, placeholder = 'নির্বাচন করুন') {
        if (!select) return;
        select.innerHTML = `<option value="">-- ${placeholder} --</option>`;
        select.disabled = true;
    }

    function populateSelect(select, items, selectedValue, labelKey = 'name_bn', subLabelKey = 'name_en') {
        if (!select) return;
        const currentVal = selectedValue || select.dataset.initialValue || '';
        const placeholder = select.getAttribute('data-placeholder') || 'নির্বাচন করুন';
        
        let html = `<option value="">-- ${placeholder} --</option>`;
        items.forEach(item => {
            const isSelected = String(item.id) === String(currentVal);
            const mainLabel = item[labelKey] || item.name_en;
            const subLabel = item[subLabelKey] && item[subLabelKey] !== mainLabel ? ` (${item[subLabelKey]})` : '';
            const extra = item.post_code ? ` [পোস্ট কোড: ${item.post_code}]` : (item.type ? ` [${item.type}]` : '');
            
            html += `<option value="${item.id}" data-postcode="${item.post_code || ''}" ${isSelected ? 'selected' : ''}>${mainLabel}${subLabel}${extra}</option>`;
        });
        
        select.innerHTML = html;
        select.disabled = false;
        select.removeAttribute('data-initial-value');
    }

    // 1. Load Districts when Division changes
    async function loadDistricts(divisionId, initialVal = null) {
        if (!divisionId) {
            clearSelect(districtSelect, 'আগে বিভাগ নির্বাচন করুন');
            clearSelect(upazilaSelect, 'আগে জেলা নির্বাচন করুন');
            clearSelect(unionSelect, 'আগে উপজেলা নির্বাচন করুন');
            clearSelect(urbanAreaSelect, 'আগে জেলা নির্বাচন করুন');
            clearSelect(postOfficeSelect, 'আগে জেলা নির্বাচন করুন');
            return;
        }

        showLoading('জেলা লোড হচ্ছে...');
        try {
            if (!cache.districts[divisionId]) {
                const res = await fetch(`/api/geo/districts?division_id=${encodeURIComponent(divisionId)}`);
                cache.districts[divisionId] = await res.json();
            }
            populateSelect(districtSelect, cache.districts[divisionId], initialVal);
            
            // If district was already selected, trigger its children
            if (districtSelect.value) {
                await loadDistrictChildren(districtSelect.value);
            }
        } catch (e) {
            console.error('Failed to load districts:', e);
        } finally {
            hideLoading();
        }
    }

    // 2. Load Upazilas, Urban Areas & Post Offices when District changes
    async function loadDistrictChildren(districtId) {
        if (!districtId) {
            clearSelect(upazilaSelect, 'আগে জেলা নির্বাচন করুন');
            clearSelect(unionSelect, 'আগে উপজেলা নির্বাচন করুন');
            clearSelect(urbanAreaSelect, 'আগে জেলা নির্বাচন করুন');
            clearSelect(postOfficeSelect, 'আগে জেলা নির্বাচন করুন');
            return;
        }

        showLoading('উপজেলা ও ডাকঘর লোড হচ্ছে...');
        try {
            // Load Upazilas
            if (!cache.upazilas[districtId]) {
                const res = await fetch(`/api/geo/upazilas?district_id=${encodeURIComponent(districtId)}`);
                cache.upazilas[districtId] = await res.json();
            }
            populateSelect(upazilaSelect, cache.upazilas[districtId], upazilaSelect.dataset.initialValue);

            // Load Urban Areas (Municipalities/City Corporations for this district)
            if (urbanAreaSelect) {
                if (!cache.urbanAreas[districtId]) {
                    const resUrb = await fetch(`/api/geo/urban-areas?district_id=${encodeURIComponent(districtId)}`);
                    cache.urbanAreas[districtId] = await resUrb.json();
                }
                populateSelect(urbanAreaSelect, cache.urbanAreas[districtId], urbanAreaSelect.dataset.initialValue);
            }

            // Load Post Offices for this district
            if (postOfficeSelect) {
                const cacheKey = `dist_${districtId}`;
                if (!cache.postOffices[cacheKey]) {
                    const resPo = await fetch(`/api/geo/post-offices?district_id=${encodeURIComponent(districtId)}`);
                    cache.postOffices[cacheKey] = await resPo.json();
                }
                populateSelect(postOfficeSelect, cache.postOffices[cacheKey], postOfficeSelect.dataset.initialValue);
            }

            // Trigger upazila children if preselected
            if (upazilaSelect.value) {
                await loadUpazilaChildren(upazilaSelect.value);
            }
        } catch (e) {
            console.error('Failed to load district children:', e);
        } finally {
            hideLoading();
        }
    }

    // 3. Load Unions when Upazila changes
    async function loadUpazilaChildren(upazilaId) {
        if (!upazilaId) {
            clearSelect(unionSelect, 'আগে উপজেলা নির্বাচন করুন');
            return;
        }

        showLoading('ইউনিয়ন লোড হচ্ছে...');
        try {
            if (unionSelect) {
                if (!cache.unions[upazilaId]) {
                    const res = await fetch(`/api/geo/unions?upazila_id=${encodeURIComponent(upazilaId)}`);
                    cache.unions[upazilaId] = await res.json();
                }
                populateSelect(unionSelect, cache.unions[upazilaId], unionSelect.dataset.initialValue);
            }

            // Also refine Post Offices for this specific Upazila if available
            if (postOfficeSelect) {
                const districtId = districtSelect.value;
                const cacheKey = `upz_${upazilaId}`;
                if (!cache.postOffices[cacheKey]) {
                    const resPo = await fetch(`/api/geo/post-offices?district_id=${encodeURIComponent(districtId)}&upazila_id=${encodeURIComponent(upazilaId)}`);
                    const poItems = await resPo.json();
                    if (poItems && poItems.length > 0) {
                        cache.postOffices[cacheKey] = poItems;
                        populateSelect(postOfficeSelect, poItems, postOfficeSelect.dataset.initialValue);
                    }
                } else {
                    populateSelect(postOfficeSelect, cache.postOffices[cacheKey], postOfficeSelect.dataset.initialValue);
                }
            }
        } catch (e) {
            console.error('Failed to load upazila children:', e);
        } finally {
            hideLoading();
        }
    }

    // Event Listeners
    divisionSelect.addEventListener('change', () => {
        clearSelect(districtSelect, 'জেলা নির্বাচন করুন');
        clearSelect(upazilaSelect, 'আগে জেলা নির্বাচন করুন');
        clearSelect(unionSelect, 'আগে উপজেলা নির্বাচন করুন');
        if (urbanAreaSelect) clearSelect(urbanAreaSelect, 'আগে জেলা নির্বাচন করুন');
        if (postOfficeSelect) clearSelect(postOfficeSelect, 'আগে জেলা নির্বাচন করুন');
        if (postalCodeInput) postalCodeInput.value = '';
        loadDistricts(divisionSelect.value);
    });

    districtSelect.addEventListener('change', () => {
        clearSelect(upazilaSelect, 'উপজেলা বা থানা নির্বাচন করুন');
        clearSelect(unionSelect, 'আগে উপজেলা নির্বাচন করুন');
        if (urbanAreaSelect) clearSelect(urbanAreaSelect, 'পৌরসভা/শহরাঞ্চল নির্বাচন করুন');
        if (postOfficeSelect) clearSelect(postOfficeSelect, 'ডাকঘর নির্বাচন করুন');
        if (postalCodeInput) postalCodeInput.value = '';
        loadDistrictChildren(districtSelect.value);
    });

    upazilaSelect.addEventListener('change', () => {
        if (unionSelect) clearSelect(unionSelect, 'ইউনিয়ন নির্বাচন করুন');
        loadUpazilaChildren(upazilaSelect.value);
    });

    if (postOfficeSelect && postalCodeInput) {
        postOfficeSelect.addEventListener('change', () => {
            const selectedOption = postOfficeSelect.options[postOfficeSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.postcode) {
                postalCodeInput.value = selectedOption.dataset.postcode;
            }
        });
    }

    // Quick filter / search input support for large lists
    const searchInputs = container.querySelectorAll('[data-geo-search]');
    searchInputs.forEach(input => {
        const targetSelectName = input.getAttribute('data-geo-search');
        const targetSelect = container.querySelector(`[data-geo="${targetSelectName}"]`);
        if (!targetSelect) return;

        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            for (let i = 1; i < targetSelect.options.length; i++) {
                const opt = targetSelect.options[i];
                const text = opt.textContent.toLowerCase();
                opt.hidden = query !== '' && !text.includes(query);
            }
        });
    });

    // Initial prefill / edit mode support
    const initialDivisionId = divisionSelect.value || divisionSelect.dataset.initialValue;
    if (initialDivisionId) {
        divisionSelect.value = initialDivisionId;
        const initialDistrictId = districtSelect.dataset.initialValue;
        loadDistricts(initialDivisionId, initialDistrictId);
    }
}

// Auto-run if container present in DOM
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        initAddressSelector('bd-address-selector');
    });
}
