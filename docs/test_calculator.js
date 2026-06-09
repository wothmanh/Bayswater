const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  
  try {
    await page.goto('http://localhost:8000/admin/quotations/create');
    await page.waitForLoadState('networkidle');
    
    // Select accommodation ID 1 which has both features enabled
    const accommodationSelect = await page.$('select[name="accommodation_id"]');
    if (accommodationSelect) {
      await accommodationSelect.selectOption('1');
      await page.waitForTimeout(1000);
    }
    
    // Check the style attributes of the divs
    const privateBathroomDiv = await page.$('#private_bathroom_div');
    const dietarySupplementDiv = await page.$('#dietary_supplement_div');
    
    const privateBathroomStyle = privateBathroomDiv ? await privateBathroomDiv.getAttribute('style') : 'not found';
    const dietarySupplementStyle = dietarySupplementDiv ? await dietarySupplementDiv.getAttribute('style') : 'not found';
    
    console.log('Private Bathroom Div Style:', privateBathroomStyle);
    console.log('Dietary Supplement Div Style:', dietarySupplementStyle);
    
    // Check if the divs are visible
    const privateBathroomVisible = privateBathroomDiv ? await privateBathroomDiv.isVisible() : false;
    const dietarySupplementVisible = dietarySupplementDiv ? await dietarySupplementDiv.isVisible() : false;
    
    console.log('Private Bathroom Div Visible:', privateBathroomVisible);
    console.log('Dietary Supplement Div Visible:', dietarySupplementVisible);
    
    // Check the selected accommodation option's data attributes
    const selectedOption = await page.$('select[name="accommodation_id"] option[value="1"]');
    if (selectedOption) {
      const privateBathroomEnabled = await selectedOption.getAttribute('data-private-bathroom-enabled');
      const dietarySupplementEnabled = await selectedOption.getAttribute('data-dietary-supplement-enabled');
      console.log('Selected option private bathroom enabled:', privateBathroomEnabled);
      console.log('Selected option dietary supplement enabled:', dietarySupplementEnabled);
    }
    
  } catch (error) {
    console.error('Error:', error.message);
  } finally {
    await browser.close();
  }
})();