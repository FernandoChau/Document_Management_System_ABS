/**
 * Clamp a zoom value between min and max.
 */
export function clampZoom(zoom: number, min = 0.25, max = 4): number {
  return Math.min(max, Math.max(min, zoom));
}

/**
 * Round zoom to 2 decimal places for display.
 */
export function formatZoom(zoom: number): string {
  return `${Math.round(zoom * 100)}%`;
}

/**
 * Step zoom in by 25%.
 */
export function zoomIn(current: number): number {
  return clampZoom(current + 0.25);
}

/**
 * Step zoom out by 25%.
 */
export function zoomOut(current: number): number {
  return clampZoom(current - 0.25);
}

/**
 * Reset zoom to 1 (100%).
 */
export const DEFAULT_ZOOM = 1;

/**
 * Normalize rotation to 0-359 degrees.
 */
export function normalizeRotation(rotation: number): number {
  return ((rotation % 360) + 360) % 360;
}

/**
 * Rotate clockwise by 90 degrees.
 */
export function rotateClockwise(current: number): number {
  return normalizeRotation(current + 90);
}

/**
 * Rotate counter-clockwise by 90 degrees.
 */
export function rotateCounterClockwise(current: number): number {
  return normalizeRotation(current - 90);
}
