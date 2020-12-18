import { translate } from 'vue-gettext';
import defaultTranslations from '../../../locales/de_DE.json';
import eventBus from './event-bus.js';

const DEFAULT_LANG = 'de_DE';
const DEFAULT_LANG_NAME = 'Deutsch';

const state = getInitialState();

const $gettext = translate.gettext.bind(translate);

export { $gettext, translate, getLocale, setLocale, getVueConfig };

function getLocale() {
    return state.locale;
}

async function setLocale(locale = getInitialLocale()) {
    if (!(locale in getInstalledLanguages())) {
        throw new Error('Invalid locale: ' + locale);
    }

    state.locale = locale;
    if (state.translations[state.locale] === null) {
        state.translations[state.locale] = await getTranslations(state.locale);
    }

    translate.initTranslations(state.translations, {
        getTextPluginMuteLanguages: [DEFAULT_LANG],
        getTextPluginSilent: false,
        language: state.locale,
        silent: false,
    });

    eventBus.emit('studip:set-locale', state.locale);
}

function getVueConfig() {
    const availableLanguages = Object.entries(getInstalledLanguages()).reduce((memo, [lang, { name }]) => {
        memo[lang] = name;

        return memo;
    }, {});

    return {
        availableLanguages,
        defaultLanguage: DEFAULT_LANG,
        muteLanguages: [DEFAULT_LANG],
        silent: false,
        translations: state.translations,
    };
}

function getInitialState() {
    const translations = Object.entries(getInstalledLanguages()).reduce((memo, [lang]) => {
        memo[lang] = lang === DEFAULT_LANG ? defaultTranslations : null;

        return memo;
    }, {});

    return {
        locale: DEFAULT_LANG,
        translations,
    };
}

function getInitialLocale() {
    for (const [lang, { selected }] of Object.entries(getInstalledLanguages())) {
        if (selected) {
            return lang;
        }
    }

    return DEFAULT_LANG;
}

function getInstalledLanguages() {
    return window?.STUDIP?.INSTALLED_LANGUAGES ?? { [DEFAULT_LANG]: { name: DEFAULT_LANG_NAME, selected: true } };
}

async function getTranslations(locale) {
    try {
        const translation = await import(`../../../locales/${locale}.json`);

        return translation;
    } catch (exception) {
        console.error('Could not load locale: "' + locale + '"', exception);

        return {};
    }
}
