const ATTEMPT_GUARD_STORAGE_KEY = "login_attempt_guard";

const DEFAULT_GUARD_STATE = {
  failedAttempts: 0,
  lockUntil: 0,
};

function parseState(rawValue) {
  if (!rawValue) {
    return { ...DEFAULT_GUARD_STATE };
  }

  try {
    const parsedValue = JSON.parse(rawValue);
    const failedAttempts = Number(parsedValue.failedAttempts ?? 0);
    const lockUntil = Number(parsedValue.lockUntil ?? 0);

    return {
      failedAttempts: Number.isFinite(failedAttempts) ? Math.max(0, failedAttempts) : 0,
      lockUntil: Number.isFinite(lockUntil) ? Math.max(0, lockUntil) : 0,
    };
  } catch {
    return { ...DEFAULT_GUARD_STATE };
  }
}

function readState() {
  const rawValue = window.localStorage.getItem(ATTEMPT_GUARD_STORAGE_KEY);
  return parseState(rawValue);
}

function saveState(state) {
  window.localStorage.setItem(ATTEMPT_GUARD_STORAGE_KEY, JSON.stringify(state));
}

function isLockExpired(state) {
  return state.lockUntil > 0 && state.lockUntil <= Date.now();
}

function normalizeState(state) {
  if (!isLockExpired(state)) {
    return state;
  }

  const resetState = { ...DEFAULT_GUARD_STATE };
  saveState(resetState);

  return resetState;
}

export function getAttemptGuardState() {
  const state = readState();
  return normalizeState(state);
}

export function resetAttemptGuardState() {
  const state = { ...DEFAULT_GUARD_STATE };
  saveState(state);
}

export function registerFailedAttempt(maxAttempts, lockDurationMs) {
  const state = getAttemptGuardState();

  const nextAttempts = state.failedAttempts + 1;
  const nextState = {
    failedAttempts: nextAttempts,
    lockUntil: state.lockUntil,
  };

  if (nextAttempts >= maxAttempts) {
    nextState.failedAttempts = maxAttempts;
    nextState.lockUntil = Date.now() + lockDurationMs;
  }

  saveState(nextState);
  return nextState;
}

export function isFormLocked(state) {
  return state.lockUntil > Date.now();
}

export function getRemainingAttempts(state, maxAttempts) {
  return Math.max(0, maxAttempts - state.failedAttempts);
}
