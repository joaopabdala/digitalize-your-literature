# 📚 Digitize Your Literature  
**Digitize Your Literature** is a tool that allows you to convert book and magazine pages into digital formats. You can copy the digitized version to your clipboard or download it as a PDF. The goal is to make texts that were previously unavailable on digital platforms more accessible.  
---  
## ⚙️ Initial Configuration  
Follow these steps to set up your environment:  
1. Go to [`aistudio.google.com/apikey`](https://aistudio.google.com/apikey) and generate your **API key**.  
2. Copy the contents of the `.env.example` file to a new file named `.env`.  
   - Add your API key to the `AI_API_KEY` variable.  
   - Make sure `SERVICE_PROVIDER` is set to `gemini`.  
3. Start the necessary services with:  
   ```bash  
   ./vendor/bin/sail up -d  
   ```  
4. Launch the development server with:  
   ```bash  
   ./vendor/bin/sail npm run dev  
   ```  
---  
## 🖼️ Screenshot  
![Screenshot](https://github.com/user-attachments/assets/3589739d-8b2b-44f5-99ab-2ccfeb552a8d)  
---  
