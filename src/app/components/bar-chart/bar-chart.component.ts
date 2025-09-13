import { Component, Input, OnInit, OnChanges, SimpleChanges, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

@Component({
  selector: 'app-bar-chart',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="card shadow h-100">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ title }}</h6>
      </div>
      <div class="card-body">
        <div class="chart-bar">
          <canvas #barCanvas></canvas>
        </div>
      </div>
    </div>
  `,
  styleUrls: ['./bar-chart.component.css']
})
export class BarChartComponent implements OnInit, OnChanges {
  @ViewChild('barCanvas') barCanvas!: ElementRef;
  @Input() title: string = '';
  @Input() data: { label: string; value: number }[] = [];
  @Input() color: string = '#4e73df';
  
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
    if (this.barCanvas && this.data.length > 0) {
      const ctx = this.barCanvas.nativeElement.getContext('2d');
      
      this.chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.data.map(item => item.label),
          datasets: [{
            label: this.title,
            data: this.data.map(item => item.value),
            backgroundColor: this.color,
            borderColor: this.color,
            borderWidth: 1
          }]
        },
        options: {
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
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