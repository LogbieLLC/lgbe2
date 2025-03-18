declare module '@inertiajs/vue3' {
  import { Component, Plugin } from 'vue';

  export interface PageProps {
    [key: string]: any;
  }

  export interface Page {
    component: string;
    props: PageProps;
    url: string;
    version: string | null;
  }

  export interface InertiaAppOptions {
    title?: (title: string) => string;
    resolve: (name: string) => Component | Promise<Component>;
    setup: (options: {
      el: Element;
      App: Component;
      props: {
        initialPage: Page;
        initialComponent: Component;
        resolveComponent: (name: string) => Component | Promise<Component>;
        titleCallback: (title: string) => string;
      };
      plugin: Plugin;
    }) => void;
    progress?: {
      delay?: number;
      color?: string;
      includeCSS?: boolean;
      showSpinner?: boolean;
    };
  }

  export function createInertiaApp(options: InertiaAppOptions): Promise<void>;
  export function usePage<T = PageProps>(): {
    props: T;
    url: string;
    component: string;
    version: string | null;
  };
  export function Link(props: any): Component;
  export function Head(props: any): Component;
}

declare module '../../vendor/tightenco/ziggy/dist/vue.m' {
  import { Plugin } from 'vue';
  export const ZiggyVue: Plugin;
}

declare global {
  interface Window {
    Ziggy: any;
  }

  // Add route function to global scope
  function route(name: string, params?: any, absolute?: boolean): string;
}
