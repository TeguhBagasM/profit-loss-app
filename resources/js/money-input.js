/**
 * Input nominal berformat Indonesia.
 *
 * Input terlihat: teks berformat ("1.250.000,50"), tanpa atribut name.
 * Input tersembunyi: nilai mentah ("1250000.50") yang dikirim ke server.
 * Konversi hanya di tampilan; server tetap menerima format mentah.
 */
export default (config = {}) => ({
    initial: typeof config.value === 'string' ? config.value : '',

    init() {
        const parsed = this.parse(String(this.initial).replace('.', ','));

        this.$refs.visible.value = this.initialDisplay(parsed);
        this.$refs.hidden.value = this.raw(parsed);
    },

    reformat(element) {
        const caret = element.selectionStart ?? element.value.length;
        const significantBefore = (element.value.slice(0, caret).match(/[\d,]/g) || []).length;

        const parsed = this.parse(element.value);

        element.value = this.display(parsed);
        this.$refs.hidden.value = this.raw(parsed);

        this.setCaret(element, significantBefore);
    },

    onPaste(event) {
        const text = event.clipboardData?.getData('text');

        if (!text) {
            return;
        }

        event.preventDefault();

        const element = event.target;
        const start = element.selectionStart ?? element.value.length;
        const end = element.selectionEnd ?? element.value.length;

        const pasted = this.normalizePastedText(text);
        const merged = element.value.slice(0, start) + pasted + element.value.slice(end);

        element.value = merged;
        const caret = start + pasted.length;
        element.setSelectionRange(caret, caret);

        this.reformat(element);
    },

    /**
     * @returns {{digits: string, fraction: string, hasComma: boolean}}
     */
    parse(value) {
        const cleaned = String(value).replace(/[^\d,]/g, '');
        const commaIndex = cleaned.indexOf(',');

        let digits = commaIndex === -1 ? cleaned : cleaned.slice(0, commaIndex);
        let fraction = commaIndex === -1 ? '' : cleaned.slice(commaIndex + 1).replace(/,/g, '');

        digits = digits.slice(0, 13).replace(/^0+(?=\d)/, '');
        fraction = fraction.slice(0, 2);

        return { digits, fraction, hasComma: commaIndex !== -1 };
    },

    display(parsed) {
        if (parsed.digits === '' && parsed.fraction === '' && !parsed.hasComma) {
            return '';
        }

        const digits = parsed.digits === '' ? '0' : parsed.digits;
        const grouped = this.group(digits);

        if (parsed.fraction !== '') {
            return `${grouped},${parsed.fraction}`;
        }

        return parsed.hasComma ? `${grouped},` : grouped;
    },

    initialDisplay(parsed) {
        if (parsed.digits === '' && parsed.fraction === '') {
            return '';
        }

        const digits = parsed.digits === '' ? '0' : parsed.digits;
        const grouped = this.group(digits);

        if (parsed.fraction === '' || /^0+$/.test(parsed.fraction)) {
            return grouped;
        }

        return `${grouped},${parsed.fraction}`;
    },

    raw(parsed) {
        const hasDigits = parsed.digits !== '';
        const hasFraction = parsed.fraction !== '';

        if (!hasDigits && !hasFraction) {
            return '';
        }

        const digits = hasDigits ? parsed.digits : '0';

        return hasFraction ? `${digits}.${parsed.fraction}` : digits;
    },

    group(digits) {
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    normalizePastedText(text) {
        const value = text.trim();

        if (value.includes(',')) {
            return value.replace(/\./g, '');
        }

        if (value.includes('.') && !/^\d{1,3}(\.\d{3})+$/.test(value)) {
            const lastDot = value.lastIndexOf('.');
            const integerPart = value.slice(0, lastDot).replace(/\./g, '');

            return `${integerPart},${value.slice(lastDot + 1)}`;
        }

        return value;
    },

    setCaret(element, significantBefore) {
        if (significantBefore === 0) {
            element.setSelectionRange(0, 0);

            return;
        }

        let seen = 0;
        let position = element.value.length;

        for (let index = 0; index < element.value.length; index += 1) {
            if (/[\d,]/.test(element.value[index])) {
                seen += 1;

                if (seen === significantBefore) {
                    position = index + 1;
                    break;
                }
            }
        }

        element.setSelectionRange(position, position);
    },
});
