'use strict';

function wireAssessment(panel) {
    const amount = panel.querySelector('[data-category="EXUDATE_AMOUNT"]');
    const type = panel.querySelector('[data-category="EXUDATE_TYPE"]');
    const updateExudate = () => {
        const dry = amount?.selectedOptions[0]?.dataset.key === 'DRY';
        if (type) {
            type.disabled = dry;
            if (dry) type.value = '';
        }
    };
    amount?.addEventListener('change', updateExudate);
    updateExudate();

    panel.querySelectorAll('[data-margin-key]').forEach(box => box.addEventListener('change', () => {
        if (!box.checked) return;
        if (box.dataset.marginKey === 'NONE_OBSERVED') {
            panel.querySelectorAll('[data-margin-key]:not([data-margin-key="NONE_OBSERVED"])').forEach(other => { other.checked = false; });
        } else {
            const none = panel.querySelector('[data-margin-key="NONE_OBSERVED"]');
            if (none) none.checked = false;
        }
    }));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-wound-assessment]').forEach(wireAssessment);

    const container = document.querySelector('[data-wounds-container]');
    document.querySelector('[data-add-wound]')?.addEventListener('click', () => {
        const source = container?.querySelector('[data-wound-card]');
        if (!source || !container) return;
        const card = source.cloneNode(true);
        const index = container.querySelectorAll('[data-wound-card]').length;
        card.querySelectorAll('input, select, textarea').forEach(field => {
            if (field.name) field.name = field.name.replace(/diagnoses\[\d+\]/, `diagnoses[${index}]`);
            if (field.type === 'checkbox') field.checked = false;
            else if (field.type !== 'hidden') field.value = '';
        });
        card.querySelector('[data-wound-title]').textContent = `الجرح ${index + 1}`;
        const remove = card.querySelector('[data-remove-wound]');
        remove.disabled = false;
        remove.addEventListener('click', () => card.remove());
        container.appendChild(card);
        wireAssessment(card.querySelector('[data-wound-assessment]'));
    });
    document.querySelectorAll('[data-remove-wound]:not([disabled])').forEach(button => button.addEventListener('click', () => button.closest('[data-wound-card]')?.remove()));

    const noComorbidities = document.querySelector('[data-no-comorbidities]');
    const pmhChoices = [...document.querySelectorAll('[data-pmh]')];
    noComorbidities?.addEventListener('change', () => { if (noComorbidities.checked) pmhChoices.forEach(item => { item.checked = false; }); });
    pmhChoices.forEach(item => item.addEventListener('change', () => { if (item.checked && noComorbidities) noComorbidities.checked = false; }));

    document.querySelectorAll('[data-structured-review-form], [data-initial-assessment-form]').forEach(form => form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.textContent = 'جارٍ الحفظ...';
        }
    }));
});
