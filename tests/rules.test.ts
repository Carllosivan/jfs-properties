import test from 'node:test';
import assert from 'node:assert/strict';

// Pure logic unit tests for JFS Properties business rules

function getHoursForPriority(priority: string) {
  switch (priority) {
    case 'critical':
      return { response: 1, resolution: 4 };
    case 'urgent':
      return { response: 2, resolution: 8 };
    case 'high':
      return { response: 4, resolution: 16 };
    case 'low':
      return { response: 12, resolution: 72 };
    default:
      return { response: 6, resolution: 24 };
  }
}

function isSlaBreached(deadlineIso: string, checkDateIso?: string): boolean {
  const deadline = new Date(deadlineIso).getTime();
  const check = checkDateIso ? new Date(checkDateIso).getTime() : Date.now();
  return check > deadline;
}

function calculateStockMovement(currentStock: number, requestedQty: number, type: 'in' | 'out'): { newStock: number; valid: boolean } {
  if (requestedQty <= 0) return { newStock: currentStock, valid: false };
  if (type === 'out') {
    if (requestedQty > currentStock) return { newStock: currentStock, valid: false };
    return { newStock: currentStock - requestedQty, valid: true };
  }
  return { newStock: currentStock + requestedQty, valid: true };
}

function calculateBudgetExecution(budgeted: number, actual: number): { percentage: number; isOverBudget: boolean } {
  if (budgeted <= 0) return { percentage: 0, isOverBudget: actual > 0 };
  const percentage = Math.round((actual / budgeted) * 100 * 10) / 10;
  return { percentage, isOverBudget: actual > budgeted };
}

test('SLA Priority calculation correctly assigns critical, urgent and normal deadlines', () => {
  const critical = getHoursForPriority('critical');
  assert.equal(critical.response, 1);
  assert.equal(critical.resolution, 4);

  const urgent = getHoursForPriority('urgent');
  assert.equal(urgent.response, 2);
  assert.equal(urgent.resolution, 8);

  const normal = getHoursForPriority('normal');
  assert.equal(normal.response, 6);
  assert.equal(normal.resolution, 24);
});

test('SLA breach checker flags overdue incidents correctly', () => {
  const pastDeadline = '2026-11-01T10:00:00Z';
  const laterDate = '2026-11-02T10:00:00Z';
  const earlierDate = '2026-10-31T10:00:00Z';

  assert.equal(isSlaBreached(pastDeadline, laterDate), true);
  assert.equal(isSlaBreached(pastDeadline, earlierDate), false);
});

test('Stock movement forbids out movements exceeding current inventory', () => {
  const initialStock = 10;
  
  // Valid withdrawal
  const validOut = calculateStockMovement(initialStock, 4, 'out');
  assert.equal(validOut.valid, true);
  assert.equal(validOut.newStock, 6);

  // Excessive withdrawal
  const invalidOut = calculateStockMovement(initialStock, 15, 'out');
  assert.equal(invalidOut.valid, false);
  assert.equal(invalidOut.newStock, 10);

  // Stock reception
  const validIn = calculateStockMovement(initialStock, 5, 'in');
  assert.equal(validIn.valid, true);
  assert.equal(validIn.newStock, 15);
});

test('Budget execution calculates variance and alerts when over budget', () => {
  const under = calculateBudgetExecution(50000, 35000);
  assert.equal(under.percentage, 70);
  assert.equal(under.isOverBudget, false);

  const over = calculateBudgetExecution(50000, 62000);
  assert.equal(over.percentage, 124);
  assert.equal(over.isOverBudget, true);
});
