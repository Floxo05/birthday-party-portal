import 'bootstrap';
import '@popperjs/core';

import { Application } from '@hotwired/stimulus';
import ChatController from './controllers/chat_controller.js';

// Initialize Stimulus and register controllers
window.Stimulus = window.Stimulus || Application.start();
window.Stimulus.register('chat', ChatController);
