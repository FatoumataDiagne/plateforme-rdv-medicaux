import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AdminSpecialitesComponent } from './admin-specialites.component';

describe('AdminSpecialitesComponent', () => {
  let component: AdminSpecialitesComponent;
  let fixture: ComponentFixture<AdminSpecialitesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AdminSpecialitesComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(AdminSpecialitesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
