declare module 'composable-middleware' {
    export function composable_middleware(components: Function[]): Function;
    export function is_protected_context(obj?: any): boolean;
    export function Middleware_Common_Object(): void;
}
