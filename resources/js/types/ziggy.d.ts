declare module '@inertiajs/vue3' {
  export function usePage<T = Record<string, any>>(): {
    props: T;
    url: string;
    component: string;
    version: string | null;
  };
}

declare module '../../vendor/tightenco/ziggy/dist/vue.m' {
  import { Plugin } from 'vue';
  export const ZiggyVue: Plugin;
}

declare global {
  interface Window {
    Ziggy: any;
  }
}
