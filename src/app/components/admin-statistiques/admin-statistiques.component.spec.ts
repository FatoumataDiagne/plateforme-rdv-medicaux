import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminStatistiquesComponent } from './admin-statistiques.component';

describe('AdminStatistiquesComponent', () => {
  let component: AdminStatistiquesComponent;
  let fixture: ComponentFixture<AdminStatistiquesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminStatistiquesComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(AdminStatistiquesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
