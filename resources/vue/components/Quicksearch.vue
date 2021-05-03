<template>
    <span :class="'quicksearch_container' + (containerclass ? ' ' + containerclass : '')">
        <input type="hidden"
               :name="name"
               :value="returnValue"
               v-if="!autocomplete">
        <input type="text"
               :name="autocomplete ? name : null"
               v-model="inputValue"
               autocomplete="off"
               @blur="reset()"
               @keydown.up="selectUp"
               @keydown.down="selectDown"
               @keydown.enter.prevent="selectByKey"
               v-bind="$attrs">
        <div class="dropdownmenu">
            <ul class="autocomplete__results" v-if="isVisible">
                <li class="autocomplete__result"
                    v-for="(result, index) in results"
                    :key="index"
                    :class="index === selected ? 'autocomplete__result--selected' : ''"
                    @click="select(result)"
                    v-html="result.item_name">
                </li>
                <li v-if="errorMessage !== null">{{errorMessage}}</li>
            </ul>
        </div>
    </span>
</template>

<script>
export default {
    name: 'quicksearch',
    props: {
        searchtype: {
            type: String,
            required: true
        },
        name: {
            type: String,
            required: true
        },
        value: {
            type: String,
            required: false,
            default: ''
        },
        needle: {
            type: String,
            required: false,
            default: ''
        },
        autocomplete: {
            type: Boolean,
            required: false,
            default: false
        },
        containerclass: {
            type: String,
            required: false,
            default: ''
        }
    },
    data () {
        return {
            searching: false,
            debounceTimeout: null,
            selected: null,
            results: [],
            errorMessage: null,
            inputValue: null,
            returnValue: null,
            initialValue: null
        };
    },
    methods: {
        initialize (value) {
            this.initialValue = value;
            this.inputValue = value;
            this.returnValue = value;
        },
        search (needle) {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                let data = []
                if ($(this.$el).closest("form").length > 0) {
                    data = $(this.$el).closest("form").serializeArray();
                }
                data.push({
                    name: "request",
                    value: needle
                });

                $.post(
                    STUDIP.URLHelper.getURL("dispatch.php/quicksearch/response/" + this.searchtype),
                    data
                ).done(response => {
                    this.selected = null;
                    this.results = response;
                    this.errorMessage = null;
                }).fail(response => {
                    this.errorMessage = response.responseText;
                }).always(() => {
                    this.searching = false;
                });

                this.searching = true;
            }, 500);
        },
        select (result) {
            this.inputValue = result.item_search_name;
            this.initialValue = this.inputValue;
            if (this.autocomplete) {
                this.returnValue = result.item_search_name;
            } else {
                this.returnValue = result.item_id;
            }
            this.results = [];

            this.$emit('input', this.returnValue);
        },
        selectUp () {
            if (this.selected > 0) {
                this.selected -= 1;
            } else if (this.selected === null) {
                this.selected = this.results.length - 1;
            } else {
                this.selected = null;
            }
        },
        selectDown () {
            if (this.selected === null) {
                this.selected = 0;
            } else if (this.selected < this.results.length - 1) {
                this.selected += 1;
            } else {
                this.selected = null;
            }
        },
        selectByKey () {
            if (this.selected !== null) {
                this.select(this.results[this.selected]);
            }
            return false;
        },
        reset (clear = false) {
            setTimeout(() => {
                this.results = [];
                this.selected = null;

                if (clear) {
                    this.returnValue = this.initialValue;
                    this.inputValue = this.initialValue;
                }
            }, clear ? 0 : 200);
        }
    },
    created () {
        this.initialize(this.autocomplete ? this.value : this.needle);
    },
    computed: {
        isVisible() {
            return this.results.length > 0 || this.errorMessage !== null;
        }
    },
    watch: {
        value (val) {
            this.reset(true);
            this.initialize(val);
        },
        inputValue (needle) {
            if (this.initialValue !== needle && needle.length > 2) {
                this.search(needle);
            }
        }
    }
}
</script>
