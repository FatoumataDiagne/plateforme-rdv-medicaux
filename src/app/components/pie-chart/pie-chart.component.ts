import { Component, Input, OnInit, OnChanges, SimpleChanges, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

@Component({
  selector: 'app-pie-chart',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="card shadow h-100">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ title }}</h6>
      </div>
      <div class="card-body">
        <div class="chart-pie pt-4 pb-2">
          <canvas #pieCanvas></canvas>
        </div>
        <div class="mt-4 text-center small">
          <span *ngFor="let item of data; let i = index" class="mr-2">
            <i class="fas fa-circle" [style.color]="colors[i]"></i> {{ item.label }}: {{ item.value }}
          </span>
        </div>
      </div>
    </div>
  `,
  styleUrls: ['./pie-chart.component.css']
})
export class PieChartComponent implements OnInit, OnChanges {
  @ViewChild('pieCanvas') pieCanvas!: ElementRef;
  @Input() title: string = '';
  @Input() data: { label: string; value: number }[] = [];
  @Input() colors: string[] = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
  
  private chart: any;

  ngOnInit() {
    this.createChart();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['data'] && this.chart) {
      this.updateChart();
    }
  }

  private createChart() {
    if (this.pieCanvas && this.data.length > 0) {
      const ctx = this.pieCanvas.nativeElement.getContext('2d');
      
      this.chart = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: this.data.map(item => item.label),
          datasets: [{
            data: this.data.map(item => item.value),
            backgroundColor: this.colors,
            hoverBackgroundColor: this.colors,
            hoverBorderColor: "rgba(234, 236, 244, 1)",
          }]
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          animation: {
            animateScale: true,
            animateRotate: true
          }
        }
      });
    }
  }

  private updateChart() {
    if (this.chart) {
      this.chart.data.labels = this.data.map(item => item.label);
      this.chart.data.datasets[0].data = this.data.map(item => item.value);
      this.chart.update();
    }
  }

  ngOnDestroy() {
    if (this.chart) {
      this.chart.destroy();
    }
  }
}