<script>
let uuid = 0;
export default {
    name: 'studip-proxied-checkbox',
    model: {
        prop: 'selected',
        event: 'change',
    },
    props: {
        name: String,
        id: String,
        type: String,
        value: {
            required: true
        },
        selected: {
            type: Array,
            required: true
        }
    },
    methods: {
        changeCollection () {
            const selected = new Set(this.selected);

            if (this.checked) {
                selected.delete(this.value);
            } else {
                selected.add(this.value);
            }

            this.$emit('change', [...selected.values()]);
        }
    },
    computed: {
        proxiedId () {
            return this.id ?? `proxied-checkbox-${uuid++}`;
        },
        checked () {
            return this.selected.includes(this.value);
        },
    },
    render (createElement) {
        const checkbox = createElement('input', {
            class: {
                'studip-checkbox': this.type === 'studip'
            },
            attrs: {
                type: 'checkbox',
                name: this.name,
                id: this.proxiedId,
                value: this.value,
            },
            domProps: {
                checked: this.checked,
            },
            on: {
                change: this.changeCollection,
            }
        });

        if (this.type !== 'studip') {
            return checkbox;
        }

        return createElement('span', {
            style: {
                display: 'contents',
            },
        }, [
            checkbox,
            createElement('label', {
                attrs: {
                    for: this.proxiedId
                }
            }),
        ]);
    }
};
</script>
