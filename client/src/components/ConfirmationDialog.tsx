import React from 'react';
import { 
    Dialog, DialogTitle, DialogContent, DialogActions, Button, Typography 
} from '@mui/material'; // Компоненты Material-UI

// Интерфейс для свойств компонента ConfirmationDialog
interface ConfirmationDialogProps {
  open: boolean; // Флаг, указывающий, открыт ли диалог
  onClose: () => void; // Функция, вызываемая при закрытии диалога (нажатие на кнопку "Отмена" или на фон)
  onConfirm: () => void; // Функция, вызываемая при подтверждении действия (нажатие на кнопку "OK")
  title: string; // Заголовок диалога
  message: string; // Сообщение, отображаемое в диалоге
}

// Компонент диалога подтверждения
const ConfirmationDialog: React.FC<ConfirmationDialogProps> = ({ open, onClose, onConfirm, title, message }) => {
  return (
    <Dialog open={open} onClose={onClose}> {/* Диалог, отображается, если open === true */}
      <DialogTitle>{title}</DialogTitle> {/* Заголовок диалога */}
      <DialogContent> {/* Содержимое диалога */}
        <Typography>{message}</Typography> {/* Отображение сообщения */}
      </DialogContent>
      <DialogActions> {/* Блок с кнопками действий */}
        <Button onClick={onClose}>Отмена</Button> {/* Кнопка "Отмена", вызывает onClose */}
        <Button onClick={onConfirm} color="primary"> {/* Кнопка "OK", вызывает onConfirm */}
          OK
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default ConfirmationDialog;