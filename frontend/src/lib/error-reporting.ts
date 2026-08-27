type ReportErrorOptions = {
  mechanism?: "manual" | "onerror" | "unhandledrejection" | "react_error_boundary";
  handled?: boolean;
  severity?: "error" | "warning" | "info";
};

/**
 * Reports an error to the console with route context. Loaders and server
 * fns commonly throw a raw Response; String(it) is the opaque
 * "[object Response]", so pull out the status and URL instead.
 */
export function reportError(
  error: unknown,
  context: Record<string, unknown> = {},
  options: ReportErrorOptions = {},
) {
  if (typeof window === "undefined") return;

  const message =
    error instanceof Response
      ? `Response ${error.status}${error.url ? ` at ${error.url}` : ""}`
      : error instanceof Error
        ? error.message
        : String(error);

  console.error(message, {
    route: window.location.pathname,
    mechanism: options.mechanism ?? "manual",
    severity: options.severity ?? "error",
    ...context,
  });
}
