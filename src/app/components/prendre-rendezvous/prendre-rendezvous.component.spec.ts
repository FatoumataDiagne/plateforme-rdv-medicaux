import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PrendreRendezvousComponent } from './prendre-rendezvous.component';

describe('PrendreRendezvousComponent', () => {
  let component: PrendreRendezvousComponent;
  let fixture: ComponentFixture<PrendreRendezvousComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PrendreRendezvousComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(PrendreRendezvousComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
