package za.ac.cput.Service;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import za.ac.cput.Repository.BookRepository;
import za.ac.cput.Domain.Book;

import java.util.List;

@Service
public class BookService {
    private final BookRepository bookRepository;

    @Autowired
    public BookService(BookRepository bookRepository) {
        this.bookRepository = bookRepository;
    }
    public Book save(Book book) {
        return bookRepository.save(book);
    }

    public Book readBookById(Long bookID) {
        return bookRepository.findById(bookID).orElse(null);
    }
    public Book updateBook(Book book) {
        if (bookRepository.existsById(book.getBookID())) {
            return bookRepository.save(book);
        }
        return null; // or throw an exception if preferred
    }
    public void deleteBook(Long bookID) {
        bookRepository.deleteById(bookID);
    }

    public List<Book> getAllBooks() {
        return bookRepository.findAll();
    }
}
