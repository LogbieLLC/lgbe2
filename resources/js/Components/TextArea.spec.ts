import { mount } from '@vue/test-utils';
import TextArea from './TextArea.vue';

describe('TextArea.vue', () => {
  it('renders textarea element when passed', () => {
    const wrapper = mount(TextArea, {
      props: {
        modelValue: 'Test content',
        id: 'test-textarea',
        name: 'test-textarea',
      }
    });
    
    // Check if textarea exists
    expect(wrapper.find('textarea').exists()).toBe(true);
    
    // Check if textarea has the correct value
    expect(wrapper.find('textarea').element.value).toBe('Test content');
    
    // Check if textarea has the correct id
    expect(wrapper.find('textarea').attributes('id')).toBe('test-textarea');
  });

  it('emits update:modelValue event when textarea value changes', async () => {
    const wrapper = mount(TextArea, {
      props: {
        modelValue: '',
        id: 'test-textarea',
        name: 'test-textarea',
      }
    });
    
    // Set the value of the textarea
    await wrapper.find('textarea').setValue('New content');
    
    // Check if the update:modelValue event was emitted with the correct value
    expect(wrapper.emitted('update:modelValue')).toBeTruthy();
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['New content']);
  });
});
