// src/app/dashboard-admin/arbitros/arbitro-modal/arbitro-modal.component.spec.ts

import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ArbitroModalComponent } from './arbitro-modal.component';

describe('ArbitroModalComponent', () => {
  let component: ArbitroModalComponent;
  let fixture: ComponentFixture<ArbitroModalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ArbitroModalComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ArbitroModalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
