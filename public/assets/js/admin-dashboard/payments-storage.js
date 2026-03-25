import { PAYMENT_STORAGE_KEY } from "./constants.js";
import { parseJsonSafe } from "./utils.js";

function readStorageValue() {
  const rawValue = window.localStorage.getItem(PAYMENT_STORAGE_KEY);
  const parsedValue = parseJsonSafe(rawValue, []);
  if (!Array.isArray(parsedValue)) {
    return [];
  }

  return parsedValue;
}

function writeStorageValue(payments) {
  window.localStorage.setItem(PAYMENT_STORAGE_KEY, JSON.stringify(payments));
}

export function getPayments() {
  return readStorageValue();
}

export function savePayment(paymentItem) {
  const payments = readStorageValue();
  payments.push(paymentItem);
  writeStorageValue(payments);

  return payments;
}

export function updatePaymentById(paymentId, updatedPayment) {
  const payments = readStorageValue();
  const index = payments.findIndex((payment) => payment.id === paymentId);
  if (index < 0) {
    return null;
  }

  payments[index] = updatedPayment;
  writeStorageValue(payments);

  return payments;
}

export function deletePaymentById(paymentId) {
  const payments = readStorageValue();
  const filteredPayments = payments.filter((payment) => payment.id !== paymentId);
  writeStorageValue(filteredPayments);

  return filteredPayments;
}
