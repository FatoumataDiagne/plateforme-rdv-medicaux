import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SpecialiteListComponent } from './specialite-list.component';

describe('SpecialiteListComponent', () => {
  let component: SpecialiteListComponent;
  let fixture: ComponentFixture<SpecialiteListComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SpecialiteListComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(SpecialiteListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
