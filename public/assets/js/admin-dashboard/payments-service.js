import { createActionButton, renderEmptyTableRow } from "./ui.js";
import { deletePaymentById, getPayments, savePayment, updatePaymentById } from "./payments-storage.js";
import { formatDateForInput, generateLocalEntityId, toNumber } from "./utils.js";

function setFieldErrorState(field, hasError) {
  if (!field) {
    return;
  }

  field.classList.toggle("input-error", hasError);
}

function clearPaymentFormErrors(paymentFormDom) {
  setFieldErrorState(paymentFormDom.studentId, false);
  setFieldErrorState(paymentFormDom.weekNumber, false);
  setFieldErrorState(paymentFormDom.amount, false);
  setFieldErrorState(paymentFormDom.paymentDate, false);
}

function hasValue(field) {
  if (!field) {
    return false;
  }

  return field.value.trim() !== "";
}

function validatePaymentForm(paymentFormDom, feedbackElement, showFeedback) {
  clearPaymentFormErrors(paymentFormDom);

  if (!hasValue(paymentFormDom.studentId)) {
    setFieldErrorState(paymentFormDom.studentId, true);
    showFeedback(feedbackElement, "El codigo de estudiante es obligatorio.", "error");
    return false;
  }

  if (!hasValue(paymentFormDom.weekNumber)) {
    setFieldErrorState(paymentFormDom.weekNumber, true);
    showFeedback(feedbackElement, "La semana es obligatoria.", "error");
    return false;
  }

  if (!hasValue(paymentFormDom.amount)) {
    setFieldErrorState(paymentFormDom.amount, true);
    showFeedback(feedbackElement, "El monto es obligatorio.", "error");
    return false;
  }

  if (!hasValue(paymentFormDom.paymentDate)) {
    setFieldErrorState(paymentFormDom.paymentDate, true);
    showFeedback(feedbackElement, "La fecha de pago es obligatoria.", "error");
    return false;
  }

  return true;
}

function resetPaymentForm(paymentFormDom) {
  paymentFormDom.form.reset();
  paymentFormDom.id.value = "";
  paymentFormDom.paymentDate.value = formatDateForInput();
  paymentFormDom.submitButton.textContent = "Guardar pago";
  clearPaymentFormErrors(paymentFormDom);
}

function mapPaymentToForm(paymentFormDom, payment) {
  paymentFormDom.id.value = payment.id ?? "";
  paymentFormDom.studentId.value = payment.student_id ?? "";
  paymentFormDom.weekNumber.value = String(payment.week_number ?? "");
  paymentFormDom.amount.value = String(payment.amount ?? "");
  paymentFormDom.paymentDate.value = payment.payment_date ?? formatDateForInput();
  paymentFormDom.submitButton.textContent = "Actualizar pago";
}

function buildPaymentPayload(paymentFormDom) {
  return {
    id: paymentFormDom.id.value.trim() || generateLocalEntityId("pay"),
    student_id: paymentFormDom.studentId.value.trim(),
    week_number: toNumber(paymentFormDom.weekNumber.value, 0),
    amount: Number(toNumber(paymentFormDom.amount.value, 0).toFixed(2)),
    payment_date: paymentFormDom.paymentDate.value,
  };
}

function renderPaymentRow(tableBody, payment, onEdit, onDelete) {
  const row = document.createElement("tr");

  const studentCell = document.createElement("td");
  studentCell.textContent = payment.student_id ?? "-";
  row.appendChild(studentCell);

  const weekCell = document.createElement("td");
  weekCell.textContent = String(payment.week_number ?? "-");
  row.appendChild(weekCell);

  const dateCell = document.createElement("td");
  dateCell.textContent = payment.payment_date ?? "-";
  row.appendChild(dateCell);

  const amountCell = document.createElement("td");
  amountCell.textContent = `S/ ${Number(payment.amount ?? 0).toFixed(2)}`;
  row.appendChild(amountCell);

  const actionsCell = document.createElement("td");
  const actionsWrap = document.createElement("div");
  actionsWrap.className = "table-actions";

  const editButton = createActionButton("Editar", "btn btn-outline-neutral", () => onEdit(payment));
  const deleteButton = createActionButton("Eliminar", "btn btn-outline-danger", () => onDelete(payment));

  actionsWrap.append(editButton, deleteButton);
  actionsCell.appendChild(actionsWrap);
  row.appendChild(actionsCell);

  tableBody.appendChild(row);
}

function renderPaymentsTable(tableBody, payments, onEdit, onDelete) {
  if (!tableBody) {
    return;
  }

  if (!Array.isArray(payments) || payments.length === 0) {
    renderEmptyTableRow(tableBody, "No hay pagos registrados en localStorage.", 5);
    return;
  }

  tableBody.innerHTML = "";
  payments.forEach((payment) => renderPaymentRow(tableBody, payment, onEdit, onDelete));
}

export function createPaymentsService(paymentFormDom, tableBody, feedbackElement, showFeedback) {
  function loadPayments() {
    const payments = getPayments();
    renderPaymentsTable(tableBody, payments, startEdition, removePayment);
  }

  function startEdition(payment) {
    mapPaymentToForm(paymentFormDom, payment);
  }

  function cancelEdition() {
    resetPaymentForm(paymentFormDom);
  }

  function submitPayment(event) {
    event.preventDefault();

    const isValid = validatePaymentForm(paymentFormDom, feedbackElement, showFeedback);
    if (!isValid) {
      return;
    }

    const paymentPayload = buildPaymentPayload(paymentFormDom);
    const isEditing = paymentFormDom.id.value.trim() !== "";

    if (isEditing) {
      const updated = updatePaymentById(paymentPayload.id, paymentPayload);
      if (updated === null) {
        showFeedback(feedbackElement, "El pago no existe para actualizar.", "error");
        return;
      }

      showFeedback(feedbackElement, "Pago actualizado en localStorage.", "success");
    } else {
      savePayment(paymentPayload);
      showFeedback(feedbackElement, "Pago registrado en localStorage.", "success");
    }

    resetPaymentForm(paymentFormDom);
    loadPayments();
  }

  function removePayment(payment) {
    const confirmed = window.confirm(`Se eliminara el pago de ${payment.student_id ?? "estudiante"}.`);
    if (!confirmed) {
      return;
    }

    deletePaymentById(payment.id ?? "");
    showFeedback(feedbackElement, "Pago eliminado de localStorage.", "success");
    loadPayments();
  }

  function bindEvents() {
    if (paymentFormDom.form) {
      paymentFormDom.form.addEventListener("submit", submitPayment);
    }

    if (paymentFormDom.cancelButton) {
      paymentFormDom.cancelButton.addEventListener("click", cancelEdition);
    }
  }

  return {
    init: () => {
      bindEvents();
      resetPaymentForm(paymentFormDom);
      loadPayments();
    },
  };
}
