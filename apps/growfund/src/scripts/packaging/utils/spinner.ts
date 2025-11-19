/**
 * Loading spinner utility for showing animated progress indicators
 */
export class LoadingSpinner {
  private spinner: string[] = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
  private current = 0;
  private interval: NodeJS.Timeout | null = null;
  private message: string;

  constructor(message: string) {
    this.message = message;
  }

  start(): void {
    if (this.interval) return;

    process.stdout.write(`${this.spinner[0]} ${this.message}`);

    this.interval = setInterval(() => {
      this.current = (this.current + 1) % this.spinner.length;
      process.stdout.write(`\r${this.spinner[this.current]} ${this.message}`);
    }, 100);
  }

  update(message: string): void {
    this.message = message;
    if (this.interval) {
      process.stdout.write(`\r${this.spinner[this.current]} ${this.message}`);
    }
  }

  succeed(message?: string): void {
    this.stop();
    process.stdout.write(`\r✅ ${message ?? this.message}\n`);
  }

  fail(message?: string): void {
    this.stop();
    process.stdout.write(`\r❌ ${message ?? this.message}\n`);
  }

  stop(): void {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
  }
}

/**
 * Progress tracker for managing multiple packaging steps
 */
export class ProgressTracker {
  private steps: { name: string; completed: boolean }[] = [];
  private currentStep = 0;

  addStep(name: string): void {
    this.steps.push({ name, completed: false });
  }

  startStep(stepName: string): LoadingSpinner {
    const stepIndex = this.steps.findIndex((step) => step.name === stepName);
    if (stepIndex !== -1) {
      this.currentStep = stepIndex;
    }

    const progress = `[${this.currentStep + 1}/${this.steps.length}]`;
    const spinner = new LoadingSpinner(`${progress} ${stepName}...`);
    spinner.start();
    return spinner;
  }

  completeStep(stepName: string): void {
    const step = this.steps.find((s) => s.name === stepName);
    if (step) {
      step.completed = true;
    }
  }

  getProgress(): string {
    const completed = this.steps.filter((s) => s.completed).length;
    const total = this.steps.length;
    const percentage = Math.round((completed / total) * 100);
    return `${completed}/${total} steps completed (${percentage}%)`;
  }
}
